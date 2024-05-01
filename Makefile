.SILENT:

COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

## shows this manual
help:
	printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	printf " make [target]\n\n"
	printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	awk '/^[a-zA-Z\-\_0-9\.@]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-26s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)


## cs-check dry run
cs-check:
	./vendor/bin/php-cs-fixer fix . --dry-run

## cs-fix fix
cs-fix:
	./vendor/bin/php-cs-fixer fix app

## stan
stan:
	vendor/bin/phpstan analyse

## Unit Test with Reports
unit-test:
	php -d zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20190902/xdebug.so -d xdebug.default_enable=on vendor/phpunit/phpunit/phpunit -c phpunit.xml --testsuite Unit

## Unit test without Report
unit-test-no-xdebug:
	php vendor/phpunit/phpunit/phpunit -c phpunit.xml --testsuite Unit

## Integration Test
integration-test:
	php vendor/phpunit/phpunit/phpunit -c phpunit.xml --no-coverage --testsuite Integration

## Unit Test and Integration Test
test: unit-test integration-test cs-fix stan

## Install IDE Helper
helper:
	php artisan ide-helper:eloquent && php artisan ide-helper:generate && php artisan ide-helper:meta && php artisan ide-helper:models

## Execute DB-Migration
db-migrate:
	php artisan migrate:fresh

## Execute DB Seeder
db-seed:
	php artisan db:seed

## Clear Clear
cache-clear:
	php artisan cache:clear && php artisan config:clear && php artisan event:clear && php artisan optimize:clear && php artisan route:clear

## Composer Install
composer:
	composer install

## Install Application
install: composer cache-clear db-migrate helper


