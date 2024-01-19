#!/usr/bin/env bash
set -euxo pipefail

# --parse-tags to parse custom service tags like 'tags: [!tagged app.myclass]'
bin/console --no-interaction lint:yaml --parse-tags src
bin/console --no-interaction lint:container
bin/console --no-interaction lint:twig src --show-deprecations
bin/console --no-interaction doctrine:schema:validate --skip-sync
