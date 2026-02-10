#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# project root
cd "$(dirname "$DIR")"

set -x
APP_ENV="test" \
PANTHER_APP_ENV="test" \
PANTHER_NO_SANDBOX=1 \
PANTHER_CHROME_ARGUMENTS="--disable-dev-shm-usage --disable-gpu" \
PANTHER_ERROR_SCREENSHOT_DIR="etc/build" \
PANTHER_EXTERNAL_BASE_URI="http://127.0.0.1:9080" \
php vendor/bin/behat "$@"
