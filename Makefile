# define standard colors
BLACK        := $(shell tput -Txterm setaf 0)
RED          := $(shell tput -Txterm setaf 1)
GREEN        := $(shell tput -Txterm setaf 2)
YELLOW       := $(shell tput -Txterm setaf 3)
PURPLE  := $(shell tput -Txterm setaf 4)
PURPLE       := $(shell tput -Txterm setaf 5)
BLUE         := $(shell tput -Txterm setaf 6)
WHITE        := $(shell tput -Txterm setaf 7)
RESET 		 := $(shell tput -Txterm sgr0)

DOCKER_COMPOSE  = docker-compose

EXEC_PHP        = $(DOCKER_COMPOSE) exec php

SYMFONY         = $(EXEC_PHP) bin/console
COMPOSER        = $(EXEC_PHP) composer

##
## Docker
## -------
##

dcd: ## Stop the docker composition
	@echo "${PURPLE}Sending SIGKILL to the containers${RESET}\n"
	$(DOCKER_COMPOSE) kill
	@echo "${PURPLE}Stopping docker composition and removing volumes and orphans${RESET}\n"
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

dcb: ## Build the docker image
	@echo "${PURPLE}Building and starting docker composition${RESET}\n"
	$(DOCKER_COMPOSE) up -d --build --force-recreate

dcup: ## Start the docker composition
	@echo "${PURPLE}Starting docker composition${RESET}\n"
	$(DOCKER_COMPOSE) up -d --remove-orphans --no-recreate

dcps: ## List the docker composition services
	@echo "${PURPLE}Listing docker composition services${RESET}\n"
	$(DOCKER_COMPOSE) ps

.PHONY: dcd dcb dcup dcps

##
## Utils
## -------
##

sf: ## Run bon/console
	$(SYMFONY) list

.PHONY: sf

##
## Project
## -------
##

start: ## Stop the project
start: dcup
	@echo "${GREEN}Done >${WHITE} Stack is ready${RESET}\n"

stop: ## Stop the project and remove generated files
stop: dcd
	@echo "${GREEN}Done >${WHITE} Stack is stopped${RESET}\n"

restart: ## Restart the project
restart: dcd dcup

build: ## Build the project
build: cache-clear db
	@echo "${PURPLE}Generating JWT public and private key pair${RESET}\n"
	./generate-jwt.sh
	@echo "${GREEN}Done >${WHITE} Stack is built${RESET}\n"

install: ## Start the project
install: dcd dcup build
	@echo "${GREEN}Done >${WHITE} Stack is now installed and ready${RESET}\n"

uninstall: ## Reset the project and remove generated files
uninstall: dcd
	@echo "${PURPLE}Removing generated files and postgresql data${RESET}\n"
	sudo rm -rf .env.local composer.lock vendor docker/postgresql/data
	@echo "${GREEN}Done >${WHITE} Stack is uninstalled${RESET}\n"

cache-clear: ## Clear cache
cache-clear: vendor
	@echo "${PURPLE}Clearing cache${RESET}\n"
	$(SYMFONY) cache:clear

config/jwt/private.pem:
	./generate-jwt.sh

no-docker:
	$(eval DOCKER_COMPOSE := \#)
	$(eval EXEC_PHP := )

.PHONY: install uninstall start stop restart build cache-clear jwt no-docker

##
## Database
## -----
##

db: ## Reset the database and load fixtures
db: vendor
	@echo "${PURPLE}Setting up database for env dev${RESET}\n"
	$(SYMFONY) doctrine:database:drop --if-exists --force
	$(SYMFONY) doctrine:database:create --if-not-exists
	$(SYMFONY) doctrine:migrations:migrate --no-interaction --allow-no-migration
	@echo "${PURPLE}Setting up database for env test${RESET}\n"
	$(SYMFONY) doctrine:database:drop --if-exists --force --env=test
	$(SYMFONY) doctrine:database:create --if-not-exists --env=test
	$(SYMFONY) doctrine:migrations:migrate --no-interaction --allow-no-migration --env=test

db-diff: ## Generate a new doctrine migration
migration: vendor
	$(SYMFONY) doctrine:migrations:diff --no-interaction

db-validate: ## Validate the doctrine ORM mapping
db-validate: vendor
	$(SYMFONY) doctrine:schema:validate

.PHONY: db db-diff db-validate

##
## Tests
## -----
##

test: ## Run unit and functional tests
test: tu tf behat

tu: ## Run unit tests
tu: vendor
	$(EXEC_PHP) bin/phpunit --exclude-group functional

tf: ## Run functional tests
tf: vendor
	$(EXEC_PHP) bin/phpunit --group functional

behat: ## Run behat functional tests
behat: vendor
	$(EXEC_PHP) vendor/bin/behat -p default -vv --stop-on-failure --colors

.PHONY: test tu tf behat

# rules based on files
composer.lock: composer.json
	@echo "${PURPLE}Update vendors${RESET}\n"
	$(COMPOSER) update --lock --no-scripts --no-interaction
	@echo "${PURPLE}Fixing permissions${RESET}\n"
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) config
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) features
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) migrations
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) public
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) src
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) templates
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) tests
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) translations
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) var
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) vendor
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) .*
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) *

vendor: composer.lock
	@echo "${PURPLE}Install vendors${RESET}\n"
	$(COMPOSER) install
	@echo "${PURPLE}Fixing permissions${RESET}\n"
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) config
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) features
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) migrations
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) public
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) src
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) templates
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) tests
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) translations
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) var
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) vendor
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) .*
	$(DOCKER_COMPOSE) run --rm php chown -R $(id -u):$(id -g) *

.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help
