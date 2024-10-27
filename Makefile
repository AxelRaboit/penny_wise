PHP = php
PHP_BIN = $(PHP) bin/console
SYMFONY  = symfony
COMPOSER = composer
PNPM = pnpm
BIN = bin/console
BIN_UNIT = bin/phpunit

PHP_STAN = vendor/bin/phpstan
PHP_CS_FIXER = $(PHP) ./tools/php-cs-fixer/vendor/bin/php-cs-fixer
TWIG_CS_FIXER = $(PHP) vendor/bin/twig-cs-fixer
RECTOR = $(PHP) vendor/bin/rector

WEBPACK_BUILD = pnpm run build
WEBPACK_WATCH = pnpm run dev --watch

TWIG_COMPONENT_DEBUG = $(BIN) debug:twig-component

# === Build Commands ===
all: help

build-webpack:
	$(WEBPACK_BUILD)

# === Development Commands ===
watch-webpack:
	$(WEBPACK_WATCH)

install:
	$(COMPOSER) install
	$(PNPM) install

debug-twig-component:
	$(PHP) $(TWIG_COMPONENT_DEBUG)

simulate-production:
	make cc-prod
	make build-webpack
	make run

# === Cache and Debug Commands ===
cc:
	$(PHP_BIN) cache:clear

cc-prod:
	$(PHP_BIN) cache:clear --env=prod

# === Symfony Commands ===
run:
	$(SYMFONY) server:start

stop:
	$(SYMFONY) server:stop

routes:
	$(PHP_BIN) debug:router --show-controllers

migration:
	$(PHP_BIN) make:migration

migrate:
	$(PHP_BIN) doctrine:migrations:migrate

migration-clean:
	$(PHP_BIN) doctrine:migrations:diff

migrate-all:
	$(PHP_BIN) doctrine:migrations:migrate --no-interaction

migrate-f:
	$(PHP_BIN) doctrine:migrations:migrate --no-interaction

migrate-prev:
	$(PHP_BIN) doctrine:migrations:migrate prev

controller:
	$(PHP_BIN) make:controller

entity:
	$(PHP_BIN) make:entity

form:
	$(PHP_BIN) make:form

# === Test and Lint Commands ===
test:
	$(PHP) $(BIN_UNIT) --testdox --debug

fix:
	make fix-twig
	make rector-fix
	make fix-php
	make stan
	make rector-fix
	make fix-php

prepare:
	make fix
	make test

stan:
	@$(PHP_STAN) analyse -c phpstan.neon --memory-limit 1G

lint-php:
	@$(PHP_CS_FIXER) fix --dry-run --config=.php-cs-fixer.dist.php

fix-php:
	$(PHP_CS_FIXER) fix --config=.php-cs-fixer.dist.php

lint-twig:
	$(TWIG_CS_FIXER)

fix-twig:
	$(TWIG_CS_FIXER) --fix

rector:
	$(RECTOR) process --dry-run -c ./rector.php

rector-fix:
	$(RECTOR) process -c ./rector.php

# === Help Command ===
.PHONY: help
help:
	@echo "Available targets:"
	@echo "  make cc                   - Clear Symfony cache"
	@echo "  make cc-prod              - Clear Symfony production cache"
	@echo "  make run                  - Start Symfony server"
	@echo "  make stop                 - Stop Symfony server"
	@echo "  make migration            - Create a new migration"
	@echo "  make migrate              - Run migrations"
	@echo "  make migrate-all          - Run all migrations without interaction"
	@echo "  make migrate-f            - Force migrations to run without interaction"
	@echo "  make migrate-prev         - Rollback to the previous migration"
	@echo "  make controller           - Generate a new controller"
	@echo "  make entity               - Generate a new entity"
	@echo "  make form                 - Generate a new form"
	@echo "  make test                 - Run unit tests with testdox output"
	@echo "  make fix                  - Fix code style issues and run tests"
	@echo "  make fix-php              - Fix PHP code style"
	@echo "  make fix-twig             - Fix Twig templates"
	@echo "  make rector-fix           - Fix code with Rector"
	@echo "  make lint-php             - Lint PHP code using PHP CS Fixer (dry-run)"
	@echo "  make lint-twig            - Lint Twig templates"
	@echo "  make debug-twig-component - Debug Twig components"
	@echo "  make simulate-production  - Run the application in production mode: Do not forget to set the environment variables to 'prod' before running this command"
