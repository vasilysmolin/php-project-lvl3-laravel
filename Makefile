lint:
	composer exec phpcs app tests routes

lint-fix:
	composer exec phpcbf app tests routes

test:
	php artisan test


test-coverage:
	XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-clover storage/logs/test/clover.xml

start:
	php artisan serve --host 0.0.0.0

setup:
	composer install
	cp -n .env.example .env|| true
	php artisan key:gen --ansi
	php artisan migrate
	php artisan db:seed
	npm install

watch:
	npm run watch

migrate:
	php artisan migrate

console:
	php artisan tinker

log:
	tail -f storage/logs/laravel.log

deploy:
	git push heroku

compose:
	docker-compose up

compose-test:
	docker-compose run web make test

compose-bash:
	docker-compose run web bash

compose-setup: compose-build
	docker-compose run web make setup

compose-build:
	docker-compose build

compose-db:
	docker-compose exec db psql -U postgres

compose-down:
	docker-compose down -v

install:
	composer install

autoload:
	composer dump-autoload

validate:
	composer validate

