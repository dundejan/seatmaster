# Variables for common paths or flags
PHPSTAN = docker-compose exec php vendor/bin/phpstan
ESLINT = ./node_modules/.bin/eslint

# Makefile for managing Symfony, Yarn, and Docker tasks for development

# Default target when you run 'make' will be to display help
.DEFAULT_GOAL := help

# Builds Docker images without using the cache for a fresh build
docker-build:
	@echo "Building fresh Docker images without any cache..."
	@docker-compose build --no-cache

# Starts Docker containers necessary for the development environment
docker-up:
	@echo "Starting Docker container for the connections to database and PHP/Nginx services..."
	@docker-compose up -d

# Installs PHP dependencies using Composer
compose:
	@echo "Installing packages with composer..."
	@docker-compose exec php composer install

# Starts the development environment, including front-end asset watcher
up: docker-up compose
	@echo "Starting front-end asset watcher..."
	@yarn watch

# Shuts down the development environment and stops Docker containers
down:
	@echo "Shutting down the development environment..."
	@docker-compose down

# Runs tests excluding Panther tests
test: docker-up
	@echo "Ensuring test database exists..."
	@docker-compose exec php bin/console doctrine:database:create --env=test --if-not-exists
	@docker-compose exec php bin/console doctrine:schema:update --env=test --force

	@echo "Deleting data from test database..."
	@docker-compose exec php bin/console doctrine:schema:drop --env=test --force --full-database
	@docker-compose exec php bin/console doctrine:schema:create --env=test

	@echo "Running non-Panther tests..."
	@docker-compose exec php bin/phpunit --exclude-group panther --testdox-html tests/_output/non-panther.html
	@echo "Non-Panther test results available at tests/_output/non-panther.html"

	@echo "Tests complete"

# Runs Panther tests, requiring a local environment setup
# Before running the Panther tests locally, you need to modify .env.test or .env.panther for another database
test-panther: docker-up
	@echo "Composer packages installation locally"
	@composer install

	@echo "Ensuring test database exists..."
	@php bin/console doctrine:database:create --env=test --if-not-exists
	@php bin/console doctrine:schema:update --env=test --force

	@echo "Deleting data from test database..."
	@php bin/console doctrine:schema:drop --env=test --force --full-database
	@php bin/console doctrine:schema:create --env=test

	@echo "Loading fixtures for Panther tests to test database..."
	@php bin/console doctrine:fixtures:load --env=test --no-interaction --group=OfficeFixtures

	@echo "Running Panther tests..."
	@php bin/phpunit --group panther --testdox-html tests/_output/panther.html
	@echo "Panther test results available at tests/_output/panther.html"

	@echo "Deleting data from test database..."
	@php bin/console doctrine:schema:drop --env=test --force --full-database
	@php bin/console doctrine:schema:create --env=test

# Analyses code using PHPStan with increased memory limit
php-stan: docker-up
	@echo "Analysing code using PHPStan..."
	@$(PHPSTAN) analyse --level 8 src tests --memory-limit=256M

# Installs Node.js dependencies if not already present
yarn-install:
	@echo "Installing Node.js packages..."
	@yarn install

# Lints JavaScript files
eslint: yarn-install
	@echo "Linting JavaScript files..."
	@output=$$($(ESLINT) assets/controllers/**/*.js); \
    	if [ -z "$$output" ]; then \
    		echo "No linting errors found."; \
    	else \
    		echo "$$output"; \
    	fi

# Runs both PHP and JavaScript analysis
analyse: php-stan eslint

# Cleans up generated files and clears Symfony cache
clean:
	@echo "Cleaning up..."
	@php bin/console cache:clear

# Rebuilds the development environment
rebuild: down clean up

# Displays available make targets and their descriptions
help:
	@echo "Available targets:"
	@echo "  docker-build  - Build Docker images without using cache."
	@echo "  docker-up     - Start Docker containers necessary for the development environment."
	@echo "  compose       - Install PHP dependencies using Composer inside Docker."
	@echo "  up            - Start the entire development environment, including Docker setup and front-end assets."
	@echo "  down          - Shut down the development environment and stop Docker containers."
	@echo "  test          - Run automated tests excluding Panther tests."
	@echo "  test-panther  - Run Panther tests, requires local PHP setup and appropriate environment configurations."
	@echo "  php-stan      - Perform PHP static analysis using PHPStan with an increased memory limit."
	@echo "  yarn-install  - Install Node.js dependencies with Yarn."
	@echo "  eslint        - Lint JavaScript files."
	@echo "  analyse       - Run both PHP and JavaScript analysis using PHPStan and ESLint."
	@echo "  clean         - Clean up generated files and clear Symfony cache."
	@echo "  rebuild       - Rebuild the entire development environment."
	@echo "  help          - Display this help message."

# Specify .PHONY to indicate that these are not files
.PHONY: docker-build docker-up compose up down test test-panther php-stan yarn-install eslint analyse clean rebuild help

