PHP = php

TAILWIND_BUILD = bin/console tailwind:build
TAILWIND_WATCH = $(TAILWIND_BUILD) --watch
TWIG_COMPONENT = bin/console debug:twig-component

all: help

tailwind:
	$(PHP) $(TAILWIND_BUILD)

tailwind-watch:
	$(PHP) $(TAILWIND_WATCH)

debug-twig-component:
	$(PHP) $(TWIG_COMPONENT)

help:
	@echo "Les cibles disponibles sont :"
	@echo "  make tailwind        - Exécuter le build de tailwind"
	@echo "  make tailwind-watch  - Exécuter le build de tailwind en mode watch"
	@echo "  make debug-twig-component - Exécuter le debug de la componente twig"

.PHONY: all tailwind tailwind-watch help
