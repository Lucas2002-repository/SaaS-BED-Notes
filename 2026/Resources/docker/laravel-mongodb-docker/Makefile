SHELL := /bin/sh

COMPOSE := docker compose
APP := $(COMPOSE) exec app
COMPOSER := $(APP) composer
ARTISAN := $(APP) php artisan

.DEFAULT_GOAL := help

.PHONY: help build up start stop down restart ps logs app-logs mongo-logs shell mongo-shell \
        install update key migrate migrate-fresh seed test test-unit test-feature \
        clear optimise permissions pull push config validate clean reset

help:
	@echo "Laravel + MongoDB Docker commands"
	@echo ""
	@echo "  make build          Build the Laravel PHP image"
	@echo "  make up             Start containers in the background"
	@echo "  make stop           Stop containers"
	@echo "  make down           Stop and remove containers"
	@echo "  make restart        Restart the stack"
	@echo "  make logs           Follow all container logs"
	@echo "  make shell          Open a shell in the PHP container"
	@echo "  make mongo-shell    Open mongosh"
	@echo "  make install        Install Composer dependencies"
	@echo "  make key            Generate the Laravel application key"
	@echo "  make migrate        Run Laravel migrations"
	@echo "  make seed           Run database seeders"
	@echo "  make test           Run the Laravel test suite"
	@echo "  make clear          Clear Laravel caches"
	@echo "  make validate       Validate the Compose file"
	@echo "  make reset          Remove containers and MongoDB data"

build:
	$(COMPOSE) build --pull

up:
	$(COMPOSE) up -d --build

start:
	$(COMPOSE) start

stop:
	$(COMPOSE) stop

down:
	$(COMPOSE) down

restart:
	$(COMPOSE) down
	$(COMPOSE) up -d

ps:
	$(COMPOSE) ps

logs:
	$(COMPOSE) logs -f

app-logs:
	$(COMPOSE) logs -f app

mongo-logs:
	$(COMPOSE) logs -f mongodb

shell:
	$(APP) sh

mongo-shell:
	$(COMPOSE) exec mongodb mongosh \
		--username "$${MONGO_ROOT_USERNAME:-root}" \
		--password "$${MONGO_ROOT_PASSWORD:-development-password}" \
		--authenticationDatabase admin

install:
	$(COMPOSER) install

update:
	$(COMPOSER) update

key:
	$(ARTISAN) key:generate

migrate:
	$(ARTISAN) migrate

migrate-fresh:
	$(ARTISAN) migrate:fresh --seed

seed:
	$(ARTISAN) db:seed

test:
	$(ARTISAN) test

test-unit:
	$(ARTISAN) test --testsuite=Unit

test-feature:
	$(ARTISAN) test --testsuite=Feature

clear:
	$(ARTISAN) optimize:clear

optimise:
	$(ARTISAN) optimize

permissions:
	$(APP) sh -c 'chown -R www-data:www-data storage bootstrap/cache && chmod -R ug+rwX storage bootstrap/cache'

pull:
	$(COMPOSE) pull

push:
	docker push ghcr.io/REPLACE_WITH_COLLEGE_ORG/laravel-php:8.4-bookworm-v1

config:
	$(COMPOSE) config

validate:
	$(COMPOSE) config --quiet

clean:
	$(COMPOSE) down --remove-orphans
	docker image prune -f

reset:
	$(COMPOSE) down --volumes --remove-orphans
