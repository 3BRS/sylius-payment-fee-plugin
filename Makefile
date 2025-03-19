phpstan:
	APP_ENV=test bin/phpstan.sh

ecs:
	APP_ENV=test bin/ecs.sh --clear-cache

fix:
	APP_ENV=test bin/ecs.sh --fix

install:
	composer install --no-interaction --no-scripts
	rm -fr tests/Application/public/media/cache && mkdir -p tests/Application/public/media/cache && chmod -R 777 tests/Application/public/media
	rm -fr tests/Application/var && mkdir -p tests/Application/var && chmod -R 777 tests/Application/var

backend: recreate_db

frontend:
	APP_ENV=test tests/Application/bin/console assets:install
	(cd tests/Application && yarn install --pure-lockfile)
	(cd tests/Application && GULP_ENV=prod yarn build)

recreate_db:
	APP_ENV=test tests/Application/bin/console doctrine:database:drop --force --if-exists
	APP_ENV=test tests/Application/bin/console doctrine:database:create
	APP_ENV=test tests/Application/bin/console doctrine:schema:update --force --complete --no-interaction

fixtures:
	@make recreate_db
	APP_ENV=test tests/Application/bin/console sylius:fixtures:load default --no-interaction

lint:
	APP_ENV=test bin/symfony-lint.sh

init: install backend frontend

tests: phpstan ecs lint

static: phpstan ecs lint

ci: init static

run:
	docker compose up --detach

php-bash:
	@make run
	docker compose exec --user 1000:1000 php bash

bash: php-bash