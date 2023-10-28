# Variables for common paths or flags
PHPSTAN = vendor/bin/phpstan
ESLINT = ./node_modules/.bin/eslint

# Makefile for managing Symfony, Yarn, and Docker tasks for development

# Default target when you run 'make' will be to display help
.DEFAULT_GOAL := help

# Target to ensure Docker is running
docker-up:
	@echo "Starting Docker..."
	@docker-compose up -d

# Target to start up the development environment
up: docker-up
	@echo "Starting up the development environment..."
	@symfony serve -d
	@yarn watch > /dev/null 2>&1 &

# Target to shut down the development environment
down:
	@echo "Shutting down the development environment..."
	@docker-compose down
	@symfony server:stop
	@pkill -f "yarn watch" || true

# Target to run tests
test: docker-up
	@echo "Running tests..."
	@php bin/phpunit --testdox-html tests/_output/index.html
	@echo "Test results available at tests/_output/index.html"

# Target to analyse PHP files
php-stan:
	@echo "Analysing code using PHPStan..."
	@$(PHPSTAN) analyse --level 8 src tests

# Target to lint JavaScript files
eslint:
	@echo "Linting JavaScript files..."
	@output=$$($(ESLINT) assets/controllers/**/*.js); \
    	if [ -z "$$output" ]; then \
    		echo "No linting errors found."; \
    	else \
    		echo "$$output"; \
    	fi

# Target to analyse code
analyse: php-stan eslint

# Target for cleanup
clean:
	@echo "Cleaning up..."
	@rm -rf var/cache/*
	@rm -rf public/build/*

# Target to rebuild the environment
rebuild: down clean up

# Target to display help
help:
	@echo "Available targets:"
	@echo "  docker-up  - Ensure Docker is running."
	@echo "  up         - Start up the development environment."
	@echo "  down       - Shut down the development environment."
	@echo "  test       - Run tests."
	@echo "  php-stan   - Analyse PHP files using PHPStan."
	@echo "  eslint     - Lint JavaScript files with ESLint."
	@echo "  analyse    - Analyse code using both PHPStan and ESLint."
	@echo "  clean      - Clean up generated files and caches."
	@echo "  rebuild    - Rebuild the development environment."
	@echo "  help       - Display this help message."

# Specify .PHONY at the end
.PHONY: docker-up up down test analyse php-stan eslint help clean rebuild