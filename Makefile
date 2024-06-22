PHP = php

TAILWIND_BUILD = bin/console tailwind:build
TAILWIND_WATCH = $(TAILWIND_BUILD) --watch

all: help

tailwind:
	$(PHP) $(TAILWIND_BUILD)

tailwind-watch:
	$(PHP) $(TAILWIND_WATCH)

help:
	@echo "Les cibles disponibles sont :"
	@echo "  make tailwind        - Exécuter le build de tailwind"
	@echo "  make tailwind-watch  - Exécuter le build de tailwind en mode watch"

.PHONY: all tailwind tailwind-watch help
