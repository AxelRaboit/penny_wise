PHP = php
SYMFONY = $(PHP) bin/console
SYMFONY_BIN  = symfony
COMPOSER = composer
PNPM = pnpm
BIN = bin/console
BIN_UNIT = bin/phpunit

PHP_STAN = vendor/bin/phpstan
PHP_CS_FIXER = $(PHP) ./tools/php-cs-fixer/vendor/bin/php-cs-fixer
TWIG_CS_FIXER = $(PHP) vendor/bin/twig-cs-fixer
RECTOR = $(PHP) vendor/bin/rector

TAILWIND_INIT = $(BIN) tailwind:init
TAILWIND_BUILD = $(BIN) tailwind:build
TAILWIND_WATCH = $(TAILWIND_BUILD) --watch

TWIG_COMPONENT = $(BIN) debug:twig-component

all: help

tailwind-init:
	$(PHP) $(TAILWIND_BUILD)

dev:
	$(PHP) $(TAILWIND_BUILD)

watch:
	$(PHP) $(TAILWIND_WATCH)

prod:
	$(PNPM) install --frozen-lockfile
	$(PHP) $(TAILWIND_BUILD)

debug-twig-component:
	$(PHP) $(TWIG_COMPONENT)

cc:
	$(SYMFONY_CONSOLE) cache:clear

run:
	$(SYMFONY_BIN) server:start

stop:
	$(SYMFONY_BIN) server:stop

routes:
	$(SYMFONY) debug:router --show-controllers

migration:
	$(SYMFONY) make:migration

migrate:
	$(SYMFONY) doctrine:migrations:migrate

migrate-f:
	$(SYMFONY) doctrine:migrations:migrate --no-interaction

migrate-prev:
	$(SYMFONY) doctrine:migrations:migrate prev

controller:
	$(SYMFONY) make:controller

entity:
	$(SYMFONY) make:entity

form:
	$(SYMFONY) make:form

test:
	$(PHP) $(BIN_UNIT) --testdox --debug

fix:
	make fix-twig
	make rector-fix
	make fix-php
	make stan
	make rector-fix
	make fix-php

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
	${RECTOR} process --dry-run -c ./rector.php

rector-fix:
	${RECTOR} process -c ./rector.php

.PHONY: help
help:
	@echo "Available targets:"
	@echo "  make dev                - Execute the tailwind build"
	@echo "  make watch              - Execute the tailwind build in watch mode"
	@echo "  make prod               - Execute the tailwind build for production"
	@echo "  make debug-twig-component - Debug the twig component"
	@echo "  make start               - Start the development server"
	@echo "  make stop               - Stop the development server"
	@echo "  make routes             - Display routes"
	@echo "  make migration          - Generate a new migration"
	@echo "  make migrate            - Execute migrations"
	@echo "  make migrate-f          - Execute migrations without interaction"
	@echo "  make migrate-prev       - Execute previous migrations"
	@echo "  make controller         - Generate a new controller"
	@echo "  make entity             - Generate a new entity"
	@echo "  make form               - Generate a new form"
	@echo "  make test               - Execute unit tests"
	@echo "  make stan               - Execute PHPStan analysis"
	@echo "  make lint-php           - Lint PHP code"
	@echo "  make fix-php            - Fix PHP code"
	@echo "  make rector             - Execute Rector"
	@echo "  make rector-fix         - Execute Rector and fix code"
