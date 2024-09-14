PHP = php
BIN = bin/console
BIN_UNIT = bin/phpunit

SYMFONY = symfony
SYMFONY_CONSOLE = $(SYMFONY) console
VENDOR_BIN_STAN = vendor/bin/phpstan

TAILWIND_INIT = $(BIN) tailwind:init
TAILWIND_BUILD = $(BIN) tailwind:build
TAILWIND_WATCH = $(TAILWIND_BUILD) --watch
TWIG_COMPONENT = $(BIN) debug:twig-component

CS_FIXER = tools/php-cs-fixer/vendor/bin/php-cs-fixer

VENDOR_BIN_RECTOR = vendor/bin/rector

all: help

tailwind-init:
	$(PHP) $(TAILWIND_BUILD)

tailwind:
	$(PHP) $(TAILWIND_BUILD)

tailwind-watch:
	$(PHP) $(TAILWIND_WATCH)

debug-twig-component:
	$(PHP) $(TWIG_COMPONENT)

cc:
	$(SYMFONY_CONSOLE) cache:clear

run:
	$(SYMFONY) serve

test:
	$(PHP) $(BIN_UNIT) --testdox --debug

stan:
	$(VENDOR_BIN_STAN) analyse src tests

csfixer:
	$(CS_FIXER) fix src

rector:
	$(VENDOR_BIN_RECTOR) process src

.PHONY: help
help:
	@echo "Available targets:"
	@echo "  make tailwind           - Execute the tailwind build"
	@echo "  make tailwind-watch     - Execute the tailwind build in watch mode"
	@echo "  make debug-twig-component - Debug the twig component"
	@echo "  make start              - Start the development server"
	@echo "  make cc                 - Clear the cache"
	@echo "  make test               - Execute unit tests"
	@echo "  make stan               - Execute PHPStan analysis"
