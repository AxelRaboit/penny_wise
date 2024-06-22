PHP = php
BIN = bin/console
BIN_UNIT = bin/phpunit
SYMFONY = symfony
SYMFONY_CONSOLE = $(SYMFONY) console

TAILWIND_BUILD = $(BIN) tailwind:build
TAILWIND_WATCH = $(TAILWIND_BUILD) --watch
TWIG_COMPONENT = $(BIN) debug:twig-component

all: help

tailwind:
	$(PHP) $(TAILWIND_BUILD)

tailwind-watch:
	$(PHP) $(TAILWIND_WATCH)

debug-twig-component:
	$(PHP) $(TWIG_COMPONENT)

start:
	$(SYMFONY) serve

test:
	$(PHP) $(BIN_UNIT) --testdox --debug

help:
	@echo "Les cibles disponibles sont :"
	@echo "  make tailwind        - Exécuter le build de tailwind"
	@echo "  make tailwind-watch  - Exécuter le build de tailwind en mode watch"
	@echo "  make debug-twig-component - Exécuter le debug de la componente twig"
	@echo "  make start           - Exécuter le serveur de développement"

.PHONY: all tailwind tailwind-watch help
