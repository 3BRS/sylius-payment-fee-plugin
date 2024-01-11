phpstan:
	bin/phpstan.sh

ecs:
	bin/ecs.sh

behat-js:
	APP_SUPPRESS_DEPRECATED_ERRORS=1 bin/behat --colors --strict --no-interaction -vvv -f progress

install:
	composer install --no-interaction --no-scripts

backend:
	bin/console doctrine:database:create --if-not-exists --no-interaction
	bin/console doctrine:migrations:sync-metadata-storage --no-interaction
	bin/console doctrine:schema:update --force --complete --no-interaction
	bin/console sylius:install --no-interaction
	bin/console sylius:fixtures:load default --no-interaction

frontend:
	(cd tests/Application && yarn install --pure-lockfile)
	(cd tests/Application && GULP_ENV=prod yarn build)

behat:
	bin/behat --colors --strict --no-interaction -vvv -f progress

lint:
	bin/symfony-lint.sh

init: install backend frontend

ci: init phpstan ecs behat lint

integration: init behat

static: install phpstan ecs lint
