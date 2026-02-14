#!/bin/bash
set -e  # Exit on error

# Backup composer.json and restore it on exit
cleanup() {
    echo ""
    echo "=== Cleanup: Restoring original composer.json ==="
    if [ -f composer.json.backup ]; then
        mv composer.json.backup composer.json
    fi
    rm -f composer.lock
}
trap cleanup EXIT

# Function to run full test suite (prefer-dist + prefer-lowest)
run_test_suite() {
    local sylius_version=$1
    local symfony_version=$2
    local composer_preference=$3

    echo ""
    echo "========================================================================"
    echo "=== Testing: Sylius ${sylius_version} + Symfony ${symfony_version} (${composer_preference}) ==="
    echo "========================================================================"
    echo ""

    # Clean up
    rm -f composer.lock
    ./bin-docker/docker-bash -c "rm -fr tests/Application/var/cache/*/*"

    # Set versions
    ./bin-docker/composer require "sylius/sylius:${sylius_version}.*" --no-interaction --no-update --no-scripts
    grep -o -E '"(symfony/[^"]+)"' composer.json | grep -v -E '(symfony/flex|symfony/webpack-encore-bundle|symfony/maker-bundle|symfony/panther)' | xargs printf "%s:${symfony_version}.* " | xargs ./bin-docker/composer require --no-interaction --no-update

    # Composer update
    ./bin-docker/composer update --no-interaction --${composer_preference} --no-plugins

    # Clear cache before yarn
    ./bin-docker/docker-bash -c "rm -fr tests/Application/var/cache/*/*"

    # Yarn install and build
    ./bin-docker/yarn --cwd tests/Application install
    GULP_ENV=prod ./bin-docker/yarn --cwd tests/Application build

    # Cache clear and warmup
    (cd tests/Application && ../../bin-docker/php bin/console cache:clear --env=test)
    (cd tests/Application && ../../bin-docker/php bin/console cache:warmup --env=test)

    # Run static analysis
    ./bin-docker/docker-bash -c "APP_ENV=dev bin/phpstan.sh"
    ./bin-docker/docker-bash -c "APP_ENV=dev bin/ecs.sh --clear-cache"
    ./bin-docker/docker-bash -c "APP_ENV=dev bin/symfony-lint.sh"

    # Run PHPUnit
    ./bin-docker/docker-bash -c "APP_ENV=dev bin/phpunit"

    # Setup test database
    (cd tests/Application && ../../bin-docker/php bin/console doctrine:database:drop --if-exists --env=test -vvv --force)
    (cd tests/Application && ../../bin-docker/php bin/console doctrine:database:create --env=test -vvv)
    (cd tests/Application && ../../bin-docker/php bin/console doctrine:schema:create --env=test -vvv)

    # Run Behat tests
    ./bin-docker/docker-bash bin/behat.sh

    echo ""
    echo "✓ Passed: Sylius ${sylius_version} + Symfony ${symfony_version} (${composer_preference})"
}

echo "=== Mimicking CircleCI Matrix Tests Locally ==="
echo ""

# Backup original composer.json
echo "Step 0: Backup original composer.json"
cp composer.json composer.json.backup

# Parse matrix parameters from CircleCI config
echo "Step 1: Parsing version matrices from .circleci/config.yml"
if [ ! -f .circleci/config.yml ]; then
    echo "Error: .circleci/config.yml not found!"
    exit 1
fi

# Extract sylius_version array from CircleCI config
# Looks for: sylius_version: [ "2.1", "2.2" ]
SYLIUS_LINE=$(grep 'sylius_version:' .circleci/config.yml | head -n 1)
if [ -z "$SYLIUS_LINE" ]; then
    echo "Error: Could not find sylius_version in .circleci/config.yml"
    exit 1
fi
# Extract versions from the array format: [ "2.1", "2.2" ]
SYLIUS_VERSIONS=($(echo "$SYLIUS_LINE" | grep -o '"[0-9.]*"' | tr -d '"'))

# Extract symfony_version array from CircleCI config
SYMFONY_LINE=$(grep 'symfony_version:' .circleci/config.yml | head -n 1)
if [ -z "$SYMFONY_LINE" ]; then
    echo "Error: Could not find symfony_version in .circleci/config.yml"
    exit 1
fi
SYMFONY_VERSIONS=($(echo "$SYMFONY_LINE" | grep -o '"[0-9.]*"' | tr -d '"'))

# Composer preferences (hardcoded as these match CircleCI steps)
COMPOSER_PREFERENCES=("prefer-dist" "prefer-lowest")

echo "  - Sylius versions: ${SYLIUS_VERSIONS[*]}"
echo "  - Symfony versions: ${SYMFONY_VERSIONS[*]}"
echo "  - Composer preferences: ${COMPOSER_PREFERENCES[*]}"
echo ""

# Run all combinations
for sylius_version in "${SYLIUS_VERSIONS[@]}"; do
    for symfony_version in "${SYMFONY_VERSIONS[@]}"; do
        for composer_preference in "${COMPOSER_PREFERENCES[@]}"; do
            # Restore composer.json for each test
            cp composer.json.backup composer.json

            run_test_suite "$sylius_version" "$symfony_version" "$composer_preference"
        done
    done
done

echo ""
echo "========================================================================"
echo "=== ALL TESTS PASSED! ==="
echo "========================================================================"
echo ""
echo "Summary:"
echo "  - Sylius versions tested: ${SYLIUS_VERSIONS[*]}"
echo "  - Symfony versions tested: ${SYMFONY_VERSIONS[*]}"
echo "  - Composer preferences tested: ${COMPOSER_PREFERENCES[*]}"
echo "  - Total combinations: $((${#SYLIUS_VERSIONS[@]} * ${#SYMFONY_VERSIONS[@]} * ${#COMPOSER_PREFERENCES[@]}))"
echo ""
