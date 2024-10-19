PHP = php
SYMFONY = $(PHP) bin/console
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

TAILWIND_INITIALIZE = $(BIN) tailwind:init
TAILWIND_BUILD = $(BIN) tailwind:build
TAILWIND_WATCH = $(BIN) tailwind:build --watch

TWIG_COMPONENT_DEBUG = $(BIN) debug:twig-component

# === Build Commands ===
all: help

init-tailwind:
	$(TAILWIND_INITIALIZE)

build-assets: build-tailwind build-webpack

build-tailwind:
	$(TAILWIND_BUILD)

build-webpack:
	$(WEBPACK_BUILD)

# === Development Commands ===
watch-assets: watch-tailwind watch-webpack

watch-tailwind:
	$(TAILWIND_WATCH)

watch-webpack:
	$(WEBPACK_WATCH)

# === Cache and Debug Commands ===
cc:
	$(SYMFONY) cache:clear

cc-prod:
	$(SYMFONY) cache:clear --env=prod

debug-twig-component:
	$(SYMFONY) debug:twig-component

# === Symfony Commands ===
run:
	$(SYMFONY) server:start

stop:
	$(SYMFONY) server:stop

routes:
	$(SYMFONY) debug:router --show-controllers

migration:
	$(SYMFONY) make:migration

migrate:
	$(SYMFONY) doctrine:migrations:migrate

migration-clean:
	$(SYMFONY) doctrine:migrations:diff

migrate-all:
	$(SYMFONY) doctrine:migrations:migrate --no-interaction

controller:
	$(SYMFONY) make:controller

entity:
	$(SYMFONY) make:entity

form:
	$(SYMFONY) make:form

# === Test and Lint Commands ===
test:
	$(PHP) $(BIN_UNIT) --testdox --debug

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
	@echo "  make init-tailwind        - Initialize Tailwind"
	@echo "  make build-assets         - Build assets using Webpack and Tailwind"
	@echo "  make watch-assets         - Watch assets with Webpack and Tailwind"
	@echo "  make cc                   - Clear Symfony cache"
	@echo "  make cc-prod              - Clear Symfony production cache"
	@echo "  make run                  - Start Symfony server"
	@echo "  make stop                 - Stop Symfony server"
	@echo "  make migration            - Create new migration"
	@echo "  make migrate              - Run migrations"
	@echo "  make controller           - Generate new controller"
	@echo "  make entity               - Generate new entity"
	@echo "  make form                 - Generate new form"
	@echo "  make test                 - Run unit tests"
	@echo "  make fix-php              - Fix PHP code style"
	@echo "  make fix-twig             - Fix Twig templates"
	@echo "  make rector-fix           - Fix code with Rector"
