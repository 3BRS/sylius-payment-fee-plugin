#!/usr/bin/env sh

# Generate host keys if not present
ssh-keygen -A

# Detach (by default), log to stderr (-e)
/usr/sbin/sshd -e

set -x

# $* concatenates arguments into a single string
CHROMIUM_FLAGS="${CHROMIUM_FLAGS} $*"
CHROME_USER=chrome
echo "${CHROMIUM_FLAGS}" | xargs sudo -u ${CHROME_USER} chromium-browser --headless
