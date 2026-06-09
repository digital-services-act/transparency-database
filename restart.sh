#!/bin/bash
# clever-start.sh
# Usage: ./clever-start.sh <APP_NAME_OR_ID>

APP_NAME="$1"

if [ -z "$APP_NAME" ]; then
  echo "Usage: $0 <APP_NAME_OR_ID>"
  exit 1
fi

# Check if you're logged in
if ! clever whoami >/dev/null 2>&1; then
  echo "Not logged in. Launching clever login..."
  clever login
fi

# Link the app (if not already linked in this folder)
if ! clever info --app "$APP_NAME" >/dev/null 2>&1; then
  echo "Linking app $APP_NAME..."
  clever link --app "$APP_NAME"
fi

# Start the app by scaling it to 1 instance (if currently stopped)
echo "Starting app $APP_NAME..."
clever scale --app "$APP_NAME" --instances 1

echo "Done. You can check its status with: clever status --app $APP_NAME"

