phpstan:
	bin/console cache:warmup
	bin/phpstan.sh

ecs:
	bin/ecs.sh

behat-js:
	bin/behat-js --colors --strict --no-interaction -vvv -f progress --tags="@javascript"

behat-no-js:
	bin/behat --colors --strict --no-interaction -vvv -f progress --tags="~@javascript"

behat:	behat-no-js	behat-js

install:
	composer install --no-interaction --no-scripts

backend-bare:
	bin/console doctrine:database:create --if-not-exists --no-interaction
	bin/console doctrine:migrations:sync-metadata-storage --no-interaction
	bin/console doctrine:schema:update --force --complete --no-interaction

backend:
	bin/console doctrine:database:create --if-not-exists --no-interaction
	bin/console sylius:install --no-interaction # create schema and fixtures
	# requires update of database schema due to plugin entities
	bin/console doctrine:migrations:sync-metadata-storage --no-interaction
	bin/console doctrine:schema:update --force --complete --no-interaction
	bin/console sylius:fixtures:load default --no-interaction

frontend:
	(cd tests/Application && yarn install --pure-lockfile)
	(cd tests/Application && GULP_ENV=prod yarn build)

lint:
	bin/symfony-lint.sh

init: install backend frontend

init-integration: install backend-bare frontend

integration: init-integration behat

static: install phpstan ecs lint

ci: static integration
