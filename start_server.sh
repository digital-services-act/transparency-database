#!/bin/bash

# Load environment variables if .env exists
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
fi

# Default port if not set in environment
PORT=${KAFKA_FORWARDER_PORT:-6666}

# Number of worker processes (2-4 x num_cores is recommended)
WORKERS=${GUNICORN_WORKERS:-4}

# Start Gunicorn
echo "Starting Kafka forwarder with Gunicorn on port $PORT with $WORKERS workers"
exec gunicorn --bind 127.0.0.1:$PORT \
     --workers $WORKERS \
     --log-level info \
     --access-logfile storage/logs/gunicorn-access.log \
     --error-logfile storage/logs/gunicorn-error.log \
     wsgi:app
