current_user  := $(shell id -u)
current_group := $(shell id -g)
BUILD_DIR     := $(PWD)
TMPDIR        := $(BUILD_DIR)/tmp
COMPOSER_FLAGS :=
DOCKER_FLAGS  := --interactive --tty
TEST_DIR      :=
REDUX_LOG     :=
UNIQUE_APP_CONTAINER := $(shell uuidgen)-app
MIGRATION_VERSION :=
APP_ENV       := dev

.DEFAULT_GOAL := ci

install-php:
	docker run --rm $(DOCKER_FLAGS) --volume $(BUILD_DIR):/app -w /app --volume ~/.composer:/composer --user $(current_user):$(current_group) composer install --ignore-platform-reqs $(COMPOSER_FLAGS)

update-php:
	docker run --rm $(DOCKER_FLAGS) --volume $(BUILD_DIR):/app -w /app --volume ~/.composer:/composer --user $(current_user):$(current_group) composer update --ignore-platform-reqs $(COMPOSER_FLAGS)

test: covers phpunit

covers:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./vendor/bin/covers-validator

phpunit:
	docker-compose run --rm --name $(UNIQUE_APP_CONTAINER)-$@ app ./vendor/bin/phpunit $(TEST_DIR)

phpunit-with-coverage:
	docker-compose -f docker-compose.yml -f docker-compose.debug.yml run --rm --name $(UNIQUE_APP_CONTAINER)-$@ app_debug ./vendor/bin/phpunit --configuration=phpunit.xml.dist --coverage-clover coverage.clover

cs:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./vendor/bin/phpcs

fix-cs:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./vendor/bin/phpcbf

stan:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app php -d memory_limit=-1 vendor/bin/phpstan analyse --level=7 --no-progress src/ tests/

phpmd:
	docker-compose run --rm --no-deps --name $(UNIQUE_APP_CONTAINER)-$@ app ./vendor/bin/phpmd src/ text phpmd.xml

ci: covers phpunit cs stan

ci-with-coverage: covers phpunit-with-coverage cs stan

.PHONY: ci ci-with-coverage covers cs install-php phpmd phpunit stan test
