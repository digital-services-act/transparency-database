#!/bin/bash
set -euo pipefail

# -------- Config --------
WORKERS_PER_QUEUE="${WORKERS_PER_QUEUE:-4}"
QUEUES=("zip" "s3copy" "csv" "default")

# Monitor tuning
QUIET_CHECKS="${QUIET_CHECKS:-3}"     # consecutive checks with 0 jobs before shutdown
CHECK_INTERVAL="${CHECK_INTERVAL:-10}" # seconds between checks

# Resilience
MAX_RESTARTS="${MAX_RESTARTS:-5}"
RESTART_BACKOFF="${RESTART_BACKOFF:-5}"  # base backoff; increases per crash, capped at 60s

# Optional seed dispatch (put your recursive dispatcher here)
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

# --- DB monitor: count all jobs (pending + delayed + reserved) for the target queues ---
queue_count() {
  local IFS=,
  local qlist="${QUEUES[*]}"
  php -r '
    require __DIR__."/vendor/autoload.php";
    $app = require __DIR__."/bootstrap/app.php";
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $queues = explode(",", getenv("QLIST"));
    $count = Illuminate\Support\Facades\DB::table("jobs")->whereIn("queue", $queues)->count();
    echo $count, PHP_EOL;
  ' QLIST="$qlist"
}

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
            --memory=16384 \
            --tries=3 \
            --queue="$queue_name"
        then
          # Normal exit only happens on SIGTERM/SIGINT; just break to stop the loop
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
  total="$(queue_count)"
  if [[ "$total" =~ ^[0-9]+$ ]]; then
    log "[monitor] jobs remaining across {${QUEUES[*]}}: $total"
  else
    log "[monitor] unable to read job count (got: $total). Keeping workers up."
    consecutive_zero=0
  fi

  if [[ "${total:-1}" -eq 0 ]]; then
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
