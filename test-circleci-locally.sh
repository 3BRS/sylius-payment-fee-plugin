#!/bin/bash
set -e  # Exit on error

# Backup composer.json and package.json and restore them on exit
cleanup() {
    echo ""
    echo "=== Cleanup: Restoring original composer.json and package.json ==="
    if [ -f composer.json.backup ]; then
        mv composer.json.backup composer.json
    fi
    if [ -f tests/Application/package.json.backup ]; then
        mv tests/Application/package.json.backup tests/Application/package.json
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
    # Sylius 2.1 and 2.2 Behat contexts need Behat 3, their admin and shop assets Encore 5
    if [[ "${sylius_version}" =~ ^2\.[12]$ ]]; then
        ./bin-docker/composer require --dev "behat/behat:^3.34" --no-interaction --no-update --no-scripts
        ./bin-docker/docker-bash -c "cd tests/Application && npm pkg set 'devDependencies.@symfony/webpack-encore=^5.0.1'"
    fi

    # Composer update
    ./bin-docker/composer update --no-interaction --${composer_preference} --no-plugins

    # Clear cache before yarn
    ./bin-docker/docker-bash -c "rm -fr tests/Application/var/cache/*/*"

    # Yarn install and build
    ./bin-docker/yarn --cwd tests/Application install
    GULP_ENV=prod ./bin-docker/yarn --cwd tests/Application build

    # Cache clear and warmup
    ./bin-docker/php bin/console cache:clear --env=test
    ./bin-docker/php bin/console cache:warmup --env=test

    # Run static analysis
    ./bin-docker/docker-bash -c "APP_ENV=dev bin/phpstan.sh"
    ./bin-docker/docker-bash -c "APP_ENV=dev bin/ecs.sh --clear-cache"
    ./bin-docker/docker-bash -c "APP_ENV=dev bin/symfony-lint.sh"

    # Run PHPUnit
    ./bin-docker/docker-bash -c "APP_ENV=dev bin/phpunit"

    # Setup test database
    ./bin-docker/php bin/console doctrine:database:drop --if-exists --env=test -vvv --force
    ./bin-docker/php bin/console doctrine:database:create --env=test -vvv
    ./bin-docker/php bin/console doctrine:schema:create --env=test -vvv

    # Run Behat tests
    ./bin-docker/docker-bash bin/behat.sh

    echo ""
    echo "✓ Passed: Sylius ${sylius_version} + Symfony ${symfony_version} (${composer_preference})"
}

echo "=== Mimicking CircleCI Matrix Tests Locally ==="
echo ""

# Backup original composer.json and package.json
echo "Step 0: Backup original composer.json and package.json"
cp composer.json composer.json.backup
cp tests/Application/package.json tests/Application/package.json.backup

# Parse matrix parameters from CircleCI config
echo "Step 1: Parsing version matrices from .circleci/config.yml"
if [ ! -f .circleci/config.yml ]; then
    echo "Error: .circleci/config.yml not found!"
    exit 1
fi

# Extract sylius_version and symfony_version arrays of every matrix entry from CircleCI config
# Looks for: sylius_version: [ "2.1", "2.2" ]
mapfile -t SYLIUS_LINES < <(grep -E 'sylius_version: \[' .circleci/config.yml)
mapfile -t SYMFONY_LINES < <(grep -E 'symfony_version: \[' .circleci/config.yml)
if [ ${#SYLIUS_LINES[@]} -eq 0 ]; then
    echo "Error: Could not find sylius_version in .circleci/config.yml"
    exit 1
fi
if [ ${#SYMFONY_LINES[@]} -ne ${#SYLIUS_LINES[@]} ]; then
    echo "Error: Could not find symfony_version for every matrix in .circleci/config.yml"
    exit 1
fi

# Composer preferences (hardcoded as these match CircleCI steps)
COMPOSER_PREFERENCES=("prefer-dist" "prefer-lowest")

COMBINATIONS=()
for i in "${!SYLIUS_LINES[@]}"; do
    # Extract versions from the array format: [ "2.1", "2.2" ]
    SYLIUS_VERSIONS=($(echo "${SYLIUS_LINES[$i]}" | grep -o '"[0-9.]*"' | tr -d '"'))
    SYMFONY_VERSIONS=($(echo "${SYMFONY_LINES[$i]}" | grep -o '"[0-9.]*"' | tr -d '"'))
    echo "  - Sylius versions: ${SYLIUS_VERSIONS[*]}, Symfony versions: ${SYMFONY_VERSIONS[*]}"
    for sylius_version in "${SYLIUS_VERSIONS[@]}"; do
        for symfony_version in "${SYMFONY_VERSIONS[@]}"; do
            COMBINATIONS+=("${sylius_version}/${symfony_version}")
        done
    done
done
echo "  - Composer preferences: ${COMPOSER_PREFERENCES[*]}"
echo ""

# Run all combinations
for combination in "${COMBINATIONS[@]}"; do
    IFS=/ read -r sylius_version symfony_version <<< "$combination"
    for composer_preference in "${COMPOSER_PREFERENCES[@]}"; do
        # Restore composer.json and package.json for each test
        cp composer.json.backup composer.json
        cp tests/Application/package.json.backup tests/Application/package.json

        run_test_suite "$sylius_version" "$symfony_version" "$composer_preference"
    done
done

echo ""
echo "========================================================================"
echo "=== ALL TESTS PASSED! ==="
echo "========================================================================"
echo ""
echo "Summary:"
echo "  - Sylius/Symfony versions tested: ${COMBINATIONS[*]}"
echo "  - Composer preferences tested: ${COMPOSER_PREFERENCES[*]}"
echo "  - Total combinations: $((${#COMBINATIONS[@]} * ${#COMPOSER_PREFERENCES[@]}))"
echo ""
