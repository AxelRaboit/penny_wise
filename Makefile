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

start:
	$(SYMFONY) serve

test:
	$(PHP) $(BIN_UNIT) --testdox --debug

stan:
	$(VENDOR_BIN_STAN) analyse src tests

help:
	@echo "Les cibles disponibles sont :"
	@echo "  make tailwind        - Exécuter le build de tailwind"
	@echo "  make tailwind-watch  - Exécuter le build de tailwind en mode watch"
	@echo "  make debug-twig-component - Exécuter le debug de la componente twig"
	@echo "  make start           - Exécuter le serveur de développement"
	@echo "  make cc               - Effacer le cache"
	@echo "  make test             - Exécuter les tests unitaires"

.PHONY: all tailwind tailwind-watch help
