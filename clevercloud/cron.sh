#!/bin/bash -l
set -euo pipefail

pushd "$APP_HOME"
php artisan schedule:run >> /dev/null 2>&1
