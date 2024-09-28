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

ASSET_MAP_COMPILE = $(BIN) asset-map:compile

TAILWIND_INITIALIZE = $(BIN) tailwind:init
TAILWIND_BUILD = $(BIN) tailwind:build
TAILWIND_DEVELOPMENT = $(TAILWIND_BUILD)
TAILWIND_WATCH_DEVELOPMENT = $(TAILWIND_BUILD) --watch

TWIG_COMPONENT_DEBUG = $(BIN) debug:twig-component

all: help

init-tailwind:
	$(PHP) $(TAILWIND_INITIALIZE)

compile-assets:
	$(PHP) $(ASSET_MAP_COMPILE)

build-tailwind:
	$(PHP) $(TAILWIND_BUILD)

dev-tailwind:
	$(PHP) $(TAILWIND_DEVELOPMENT)

watch-tailwind:
	$(PHP) $(TAILWIND_WATCH_DEVELOPMENT)

watch-all-assets:
	$(PHP) $(ASSET_MAP_COMPILE)
	$(PHP) $(TAILWIND_WATCH_DEVELOPMENT)

assets-build:
	$(PHP) $(ASSET_MAP_COMPILE)
	$(PHP) $(TAILWIND_BUILD)

prod-build:
	$(PNPM) install --frozen-lockfile
	$(PHP) $(ASSET_MAP_COMPILE)
	$(PHP) $(TAILWIND_BUILD)

install:
	$(COMPOSER) install
	$(PNPM) install
	make init-tailwind

debug-twig-component:
	$(PHP) $(TWIG_COMPONENT_DEBUG)

cc:
	$(SYMFONY) cache:clear

cc-prod:
	$(SYMFONY) cache:clear --env=prod

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

migrate-all:
	$(SYMFONY) doctrine:migrations:migrate --no-interaction

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
	${RECTOR} process --dry-run -c ./rector.php

rector-fix:
	${RECTOR} process -c ./rector.php

.PHONY: help
help:
	@echo "Available targets:"
	@echo "  make init-tailwind        - Initialize the Tailwind build"
	@echo "  make compile-assets       - Compile AssetMapper assets"
	@echo "  make build-tailwind       - Execute the Tailwind build for production"
	@echo "  make watch-tailwind       - Execute the Tailwind build in watch mode"
	@echo "  make watch-all-assets     - Compile assets and execute the Tailwind build in watch mode"
	@echo "  make assets-build         - Compile AssetMapper assets and Tailwind build"
	@echo "  make prod-build           - Install dependencies and execute the build for production"
	@echo "  make install              - Install project dependencies using pnpm"
	@echo "  make debug-twig-component - Debug the Twig component"
	@echo "  make cc                   - Clear the cache"
	@echo "  make cc-prod              - Clear the production cache"
	@echo "  make run                  - Start the development server"
	@echo "  make stop                 - Stop the development server"
	@echo "  make routes               - Display routes with controllers"
	@echo "  make migration            - Generate a new migration"
	@echo "  make migrate              - Execute migrations"
	@echo "  make migrate-all          - Execute all migrations without interaction"
	@echo "  make migrate-f            - Execute migrations without interaction"
	@echo "  make migrate-prev         - Execute previous migration"
	@echo "  make controller           - Generate a new controller"
	@echo "  make entity               - Generate a new entity"
	@echo "  make form                 - Generate a new form"
	@echo "  make test                 - Execute unit tests with testdox output"
	@echo "  make fix                  - Fix code quality issues and run tests"
	@echo "  make prepare              - Run fix and unit tests"
	@echo "  make stan                 - Execute PHPStan analysis"
	@echo "  make lint-php             - Lint PHP code using PHP CS Fixer (dry-run)"
	@echo "  make fix-php              - Fix PHP code using PHP CS Fixer"
	@echo "  make lint-twig            - Lint Twig templates"
	@echo "  make fix-twig             - Fix Twig templates"
	@echo "  make rector               - Execute Rector (dry-run)"
	@echo "  make rector-fix           - Execute Rector and fix code"
