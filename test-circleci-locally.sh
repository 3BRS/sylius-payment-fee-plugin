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

# Matrix parameters from CircleCI config
SYLIUS_VERSIONS=("2.1" "2.2")
SYMFONY_VERSIONS=("6.4" "7.4")
COMPOSER_PREFERENCES=("prefer-dist" "prefer-lowest")

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
