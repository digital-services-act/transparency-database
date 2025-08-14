#!/bin/bash
set -euo pipefail

# -------- Config --------
WORKERS_PER_QUEUE="${WORKERS_PER_QUEUE:-4}"
MAX_RESTARTS="${MAX_RESTARTS:-5}"        # restart limit on crash (non-zero exit)
RESTART_BACKOFF="${RESTART_BACKOFF:-5}"  # base seconds; grows per crash, capped
QUEUES=("zip" "s3copy" "csv" "default")
DISPATCH_CMD="${DISPATCH_CMD:-php artisan statements:day-archive-z}"  # your seed job(s)
# ------------------------

PIDS=()

cleanup() {
  echo "Stopping all workers..."
  for pid in "${PIDS[@]:-}"; do
    kill "$pid" 2>/dev/null || true
  done
  # Drain waits to avoid zombies
  for pid in "${PIDS[@]:-}"; do
    wait "$pid" 2>/dev/null || true
  done
}
trap cleanup TERM INT

# Optionally dispatch initial (recursive) jobs
$DISPATCH_CMD || true

start_workers() {
  local queue_name=$1
  local count=$2

  for i in $(seq 1 "$count"); do
    (
      local crashes=0
      while true; do
        echo "[$queue_name][$i] starting worker..."
        # --stop-when-empty makes a clean (0) exit once the queue fully drains
        if php artisan queue:work database \
            --sleep=10 \
            --quiet \
            --timeout=7200 \
            --delay=10 \
            --memory=16384 \
            --tries=3 \
            --stop-when-empty \
            --queue="$queue_name"
        then
          echo "[$queue_name][$i] exited cleanly (queue empty)."
          break
        else
          ((crashes+=1))
          if (( crashes > MAX_RESTARTS )); then
            echo "[$queue_name][$i] crashed $crashes times. Giving up."
            exit 1
          fi
          # Progressive backoff: min 5s, max 60s
          local delay=$(( RESTART_BACKOFF * crashes ))
          (( delay < 5 ))  && delay=5
          (( delay > 60 )) && delay=60
          echo "[$queue_name][$i] crashed ($crashes/$MAX_RESTARTS). Restarting in ${delay}s..."
          sleep "$delay"
        fi
      done
    ) &
    PIDS+=($!)
  done
}

# Start N workers per queue
for q in "${QUEUES[@]}"; do
  start_workers "$q" "$WORKERS_PER_QUEUE"
done

# Keep process alive until all workers have terminated (your original pattern)
for pid in "${PIDS[@]}"; do
  if kill -0 "$pid" 2>/dev/null; then
    wait "$pid"
  fi
done

echo "All queue workers have stopped. Queues drained."
