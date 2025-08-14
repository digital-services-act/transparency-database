#!/bin/bash
set -euo pipefail

# -------- Config --------
WORKERS_PER_QUEUE="${WORKERS_PER_QUEUE:-4}"
QUEUES=("zip" "s3copy" "csv" "default")

# Monitor tuning
QUIET_CHECKS="${QUIET_CHECKS:-3}"        # consecutive checks with 0 jobs before shutdown
CHECK_INTERVAL="${CHECK_INTERVAL:-10}"   # seconds between checks

# Resilience
MAX_RESTARTS="${MAX_RESTARTS:-5}"
RESTART_BACKOFF="${RESTART_BACKOFF:-5}"  # base backoff; increases per crash, capped at 60s

# Optional seed dispatch (your recursive dispatcher)
DISPATCH_CMD="${DISPATCH_CMD:-php artisan statements:day-archive-z}"
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
  local IFS=,; local qlist="${QUEUES[*]}"
  php -r '
    require __DIR__."/vendor/autoload.php";
    $app = require __DIR__."/bootstrap/app.php";
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    $qc = config("queue.connections.database");
    $table = $qc["table"] ?? "jobs";
    $conn  = $qc["connection"] ?? null;

    $queues = explode(",", getenv("QLIST"));
    $db = $conn ? Illuminate\Support\Facades\DB::connection($conn)
                : Illuminate\Support\Facades\DB::connection();

    $total = $db->table($table)->whereIn("queue", $queues)->count();
    echo $total, PHP_EOL;
  ' QLIST="$qlist"
}

queue_breakdown() {
  local IFS=,; local qlist="${QUEUES[*]}"
  php -r '
    require __DIR__."/vendor/autoload.php";
    $app = require __DIR__."/bootstrap/app.php";
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    $qc = config("queue.connections.database");
    $table = $qc["table"] ?? "jobs";
    $conn  = $qc["connection"] ?? null;

    $queues = explode(",", getenv("QLIST"));
    $db = $conn ? Illuminate\Support\Facades\DB::connection($conn)
                : Illuminate\Support\Facades\DB::connection();

    $rows = $db->table($table)
               ->selectRaw("queue, COUNT(*) as c")
               ->whereIn("queue", $queues)
               ->groupBy("queue")
               ->pluck("c", "queue")
               ->all();

    $total = 0;
    foreach ($queues as $q) {
      $c = (int)($rows[$q] ?? 0);
      $total += $c;
      echo $q, "=", $c, " ";
    }
    echo "total=", $total, PHP_EOL;
  ' QLIST="$qlist"
}
# ---------------------------------------------------------------------------

# Optional: dispatch initial recursive jobs
$DISPATCH_CMD || true

# --- start N workers per queue, auto-restarting on crash ---
start_workers() {
  local queue_name=$1 count=$2
  for i in $(seq 1 "$count"); do
    (
      local crashes=0
      while true; do
        log "[$queue_name][$i] starting worker..."
        if php artisan queue:work database \
            --sleep=10 \
            --quiet \
            --timeout=7200 \
            --delay=10 \
            --memory=4096 \
            --tries=3 \
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

for q in "${QUEUES[@]}"; do
  start_workers "$q" "$WORKERS_PER_QUEUE"
done

# --- Drain monitor: only stop when queues are really empty for a while ---
consecutive_zero=0
while true; do
  # Detailed, per-queue snapshot
  breakdown="$(queue_breakdown || echo "unavailable")"
  log "[monitor] $breakdown"

  # Numeric total for exit logic
  total="$(queue_total || echo __ERR__)"

  if [[ "$total" =~ ^[0-9]+$ && "$total" -eq 0 ]]; then
    ((consecutive_zero+=1))
    if (( consecutive_zero >= QUIET_CHECKS )); then
      log "No jobs for ${QUIET_CHECKS} consecutive checks. Shutting down workers..."
      cleanup
      break
    fi
  else
    consecutive_zero=0
  fi

  sleep "$CHECK_INTERVAL"
done

log "All queue workers have stopped. Queues drained."
