#!/bin/bash
set -euo pipefail

# -------- Config --------
WORKERS_PER_QUEUE="${WORKERS_PER_QUEUE:-8}"
QUEUES=("zip" "s3copy" "csv" "archive" "sha1" )

# Phases: commands that enqueue async jobs
# Phase 1 runs first; only when queues drain do we run Phase 2.
DISPATCH_PHASE1=(
  "php artisan statements:elastic-index-date-seq yesterday 2000"
)
DISPATCH_PHASE2=(
  "php artisan statements:day-archive-z"
)
DISPATCH_PHASE3=(
  "php artisan aggregates-freeze 160"
  "php artisan aggregates-freeze 20"
  "php artisan aggregates-freeze yesterday"
)


# Monitor tuning
QUIET_CHECKS="${QUIET_CHECKS:-3}"        # stop after N consecutive zero-count checks
CHECK_INTERVAL="${CHECK_INTERVAL:-10}"   # seconds between checks
MONITOR_ALL_QUEUES="${MONITOR_ALL_QUEUES:-0}"  # set 1 to count ALL queues in jobs table

# Resilience
MAX_RESTARTS="${MAX_RESTARTS:-5}"
RESTART_BACKOFF="${RESTART_BACKOFF:-5}"  # base backoff; grows per crash up to 60s
# ------------------------

PIDS=()
log() { printf "%s: %s\n" "$(date -u +"%Y-%m-%dT%H:%M:%S.%3NZ")" "$*"; }

cleanup() {
  log "Stopping all workers..."
  for pid in "${PIDS[@]:-}"; do kill "$pid" 2>/dev/null || true; done
  for pid in "${PIDS[@]:-}"; do wait "$pid" 2>/dev/null || true; done
}
trap cleanup TERM INT

# ---- Monitor helpers (use SAME connection+table as queue:work database) ----
queue_total() {
  if [[ "$MONITOR_ALL_QUEUES" == "1" ]]; then
    php -r '
      require __DIR__."/vendor/autoload.php";
      $app = require __DIR__."/bootstrap/app.php";
      $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
      $qc = config("queue.connections.database");
      $table = $qc["table"] ?? "jobs";
      $conn  = $qc["connection"] ?? null;
      $db = $conn ? Illuminate\Support\Facades\DB::connection($conn)
                  : Illuminate\Support\Facades\DB::connection();
      echo $db->table($table)->count(), PHP_EOL;
    '
  else
    local IFS=,; local qlist="${QUEUES[*]}"
    QLIST="$qlist" php -r '
      require __DIR__."/vendor/autoload.php";
      $app = require __DIR__."/bootstrap/app.php";
      $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

      $env = getenv("QLIST");
      if ($env === false || $env === "") { fwrite(STDERR, "[monitor] QLIST is empty\n"); exit(2); }

      $qc = config("queue.connections.database");
      $table = $qc["table"] ?? "jobs";
      $conn  = $qc["connection"] ?? null;

      $queues = array_values(array_filter(array_map("trim", explode(",", $env)), "strlen"));
      if (empty($queues)) { fwrite(STDERR, "[monitor] No queues parsed from QLIST\n"); exit(2); }

      $db = $conn ? Illuminate\Support\Facades\DB::connection($conn)
                  : Illuminate\Support\Facades\DB::connection();

      echo $db->table($table)->whereIn("queue", $queues)->count(), PHP_EOL;
    '
  fi
}

queue_breakdown() {
  if [[ "$MONITOR_ALL_QUEUES" == "1" ]]; then
    php -r '
      require __DIR__."/vendor/autoload.php";
      $app = require __DIR__."/bootstrap/app.php";
      $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
      $qc = config("queue.connections.database");
      $table = $qc["table"] ?? "jobs";
      $conn  = $qc["connection"] ?? null;
      $db = $conn ? Illuminate\Support\Facades\DB::connection($conn)
                  : Illuminate\Support\Facades\DB::connection();

      $rows = $db->table($table)->selectRaw("queue, COUNT(*) as c")->groupBy("queue")->pluck("c","queue")->all();
      $total = 0;
      foreach ($rows as $q => $c) { $total += (int)$c; echo $q, "=", (int)$c, " "; }
      echo "total=", $total, PHP_EOL;
    '
  else
    local IFS=,; local qlist="${QUEUES[*]}"
    QLIST="$qlist" php -r '
      require __DIR__."/vendor/autoload.php";
      $app = require __DIR__."/bootstrap/app.php";
      $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

      $env = getenv("QLIST");
      if ($env === false || $env === "") { echo "queues=<empty> total=?\n"; exit(2); }

      $qc = config("queue.connections.database");
      $table = $qc["table"] ?? "jobs";
      $conn  = $qc["connection"] ?? null;

      $queues = array_values(array_filter(array_map("trim", explode(",", $env)), "strlen"));
      if (empty($queues)) { echo "queues=<none> total=?\n"; exit(2); }

      $db = $conn ? Illuminate\Support\Facades\DB::connection($conn)
                  : Illuminate\Support\Facades\DB::connection();

      $rows = $db->table($table)->selectRaw("queue, COUNT(*) as c")->whereIn("queue", $queues)->groupBy("queue")->pluck("c", "queue")->all();

      $total = 0;
      foreach ($queues as $q) { $c = (int)($rows[$q] ?? 0); $total += $c; echo $q, "=", $c, " "; }
      echo "total=", $total, PHP_EOL;
    '
  fi
}
# ---------------------------------------------------------------------------

# --- Start workers (auto-restart on crash) ---
start_workers() {
  local queue_name=$1 count=$2
  for i in $(seq 1 "$count"); do
    (
      local crashes=0
      while true; do
        log "[$queue_name][$i] starting worker..."
        if php artisan queue:work database \
            --sleep=10 \
            --timeout=120 \
            --delay=10 \
            --memory=16384 \
            --queue="$queue_name"
        then
          log "[$queue_name][$i] exited cleanly."
          break
        else
          ((crashes+=1))
          if (( crashes > MAX_RESTARTS )); then
            log "[$queue_name][$i] crashed $crashes times. Giving up."
            exit 1
          fi
          local delay=$(( RESTART_BACKOFF * crashes ))
          (( delay < 5 ))  && delay=5
          (( delay > 60 )) && delay=60
          log "[$queue_name][$i] crashed ($crashes/$MAX_RESTARTS). Restarting in ${delay}s..."
          sleep "$delay"
        fi
      done
    ) &
    PIDS+=($!)
  done
}

for q in "${QUEUES[@]}"; do start_workers "$q" "$WORKERS_PER_QUEUE"; done

wait_until_drained() {
  local consecutive_zero=0
  while true; do
    local breakdown total
    breakdown="$(queue_breakdown || echo "unavailable")"
    log "[monitor] $breakdown"

    total="$(queue_total || echo __ERR__)"
    if [[ "$total" =~ ^[0-9]+$ && "$total" -eq 0 ]]; then
      ((consecutive_zero+=1))
      if (( consecutive_zero >= QUIET_CHECKS )); then
        log "No jobs for ${QUIET_CHECKS} consecutive checks."
        break
      fi
    else
      consecutive_zero=0
    fi
    sleep "$CHECK_INTERVAL"
  done
}

run_phase() {
  local name="$1"; shift
  log "[phase:$name] dispatching…"
  for cmd in "$@"; do
    log "[dispatch] $cmd"
    bash -lc "$cmd"
  done
  log "[phase:$name] waiting for queues to drain…"
  wait_until_drained
  log "[phase:$name] complete."
}

# ----- SEQUENCE -----
# Phase 1
run_phase "1" "${DISPATCH_PHASE1[@]}"
# Phase 2 (only starts after phase 1 drained)
run_phase "2" "${DISPATCH_PHASE2[@]}"
# Phase 3 (only starts after phase 2 drained)
run_phase "3" "${DISPATCH_PHASE3[@]}"

# All done: stop workers and exit
cleanup
log "All queue workers have stopped. All phases complete. Version 0.8"
