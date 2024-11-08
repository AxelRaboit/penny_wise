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
	$(COMPOSER) install --working-dir=tools/php-cs-fixer
	$(PNPM) install

update:
	$(COMPOSER) update
	$(COMPOSER) update --working-dir=tools/php-cs-fixer

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

run-no-tls:
	$(SYMFONY) server:start --no-tls -d

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

recipes-update:
	$(COMPOSER) recipes:update

# === Test and Lint Commands ===
test:
	$(PHP) $(BIN_UNIT) --testdox --debug

fix:
	make fix-js
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

lint-js:
	$(PNPM) eslint --config eslint.config.cjs

fix-js:
	$(PNPM) eslint --config eslint.config.cjs --fix

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
	@echo ""
	@echo "== Build Commands =="
	@echo "  make build-webpack           - Build assets with Webpack"
	@echo "  make watch-webpack           - Watch assets for changes and rebuild with Webpack"
	@echo ""
	@echo "== Development Commands =="
	@echo "  make install                 - Install PHP and JS dependencies"
	@echo "  make update                  - Update PHP and JS dependencies"
	@echo "  make debug-twig-component    - Debug Symfony Twig components"
	@echo "  make simulate-production     - Prepare for production: clear cache, build assets, and run server"
	@echo ""
	@echo "== Cache and Debug Commands =="
	@echo "  make cc                      - Clear Symfony cache"
	@echo "  make cc-prod                 - Clear Symfony cache for production"
	@echo ""
	@echo "== Symfony Server Commands =="
	@echo "  make run                     - Start Symfony server"
	@echo "  make run-no-tls              - Start Symfony server without TLS"
	@echo "  make stop                    - Stop Symfony server"
	@echo ""
	@echo "== Routing and Migration Commands =="
	@echo "  make routes                  - List all routes with controllers"
	@echo "  make migration               - Generate a new database migration"
	@echo "  make migrate                 - Execute pending migrations"
	@echo "  make migrate-all             - Run all migrations without prompts"
	@echo "  make migrate-f               - Force-run migrations without prompts"
	@echo "  make migrate-prev            - Rollback the last migration"
	@echo "  make migration-clean         - Generate migration diff for cleaning"
	@echo ""
	@echo "== Symfony Code Generation Commands =="
	@echo "  make controller              - Create a new controller"
	@echo "  make entity                  - Create a new entity"
	@echo "  make form                    - Create a new form"
	@echo ""
	@echo "== Code Quality and Testing Commands =="
	@echo "  make test                    - Run unit tests with detailed output"
	@echo "  make fix                     - Fix code style issues (PHP/JS/Twig) and run tests"
	@echo "  make prepare                 - Run fix and then test"
	@echo "  make lint-php                - Lint PHP code (dry-run)"
	@echo "  make fix-php                 - Auto-fix PHP code style"
	@echo "  make lint-js                 - Lint JavaScript code"
	@echo "  make fix-js                  - Auto-fix JavaScript code"
	@echo "  make lint-twig               - Lint Twig templates"
	@echo "  make fix-twig                - Auto-fix Twig templates"
	@echo "  make rector                  - Preview Rector fixes (dry-run)"
	@echo "  make rector-fix              - Apply Rector fixes"
	@echo "  make stan                    - Run PHPStan static analysis"
	@echo ""
	@echo "== Symfony Recipes =="
	@echo "  make recipes-update          - Update Symfony recipes"
