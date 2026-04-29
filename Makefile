# Laravel Docker Makefile

# Default target
.PHONY: help
help:
	@echo "Laravel Docker Commands:"
	@echo ""
	@echo "Installation Commands:"
	@echo "  make install         - First-time installation (build, storage link, migrate, seed, passport setup)"
	@echo "  make reinstall       - Reinstall (rebuild containers, reset DB, regenerate keys)"
	@echo ""
	@echo "Docker Commands:"
	@echo "  make up              - Start containers"
	@echo "  make down            - Stop containers"
	@echo "  make build           - Build containers without cache"
	@echo "  make bash            - Open bash shell in Laravel container" 
	@echo "  make logs            - View logs"
	@echo ""
	@echo "Laravel Commands:"
	@echo "  make migrate         - Run migrations"
	@echo "  make seed            - Run seeder"
	@echo "  make fresh           - Refresh migrations and seed"
	@echo "  make storage-link    - Create storage link"
	@echo "  make passport        - Setup Passport"
	@echo "  make clear           - Clear all caches"
	@echo "  make test            - Run tests"
	@echo ""

# Installation Commands
.PHONY: install
install: build storage-link fresh passport

.PHONY: reinstall
reinstall: build up fresh passport-keys

# Docker Commands
.PHONY: up
up:
	docker compose up -d

.PHONY: down
down:
	docker compose down

.PHONY: build
build:
	docker compose build --no-cache

.PHONY: bash
bash:
	docker compose exec -it app bash

.PHONY: logs
logs:
	docker compose logs -f

# Laravel Commands
.PHONY: migrate
migrate:
	docker compose exec -it app php artisan migrate

.PHONY: seed
seed:
	docker compose exec -it app php artisan db:seed

.PHONY: fresh
fresh:
	docker compose exec -it app php artisan migrate:refresh --seed
	docker compose exec -it app php artisan passport:keys --force
	docker compose exec -it app php artisan passport:client --personal

.PHONY: storage-link
storage-link:
	docker compose exec -it app php artisan storage:link

.PHONY: passport
passport: passport-client passport-keys

.PHONY: passport-client
passport-client:
	docker compose exec -it app php artisan passport:client --personal

.PHONY: passport-keys
passport-keys:
	docker compose exec -it app php artisan passport:keys --force

.PHONY: clear
clear:
	docker compose exec -it app php artisan cache:clear
	docker compose exec -it app php artisan config:clear
	docker compose exec -it app php artisan route:clear
	docker compose exec -it app php artisan view:clear

.PHONY: test
test:
	docker compose exec -it app php artisan test