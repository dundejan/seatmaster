# Use the official PHP image with FPM
FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear out the local repository of retrieved package files
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring

# Check installed PHP modules
RUN php -m

# Copy Symfony application into container
COPY . /var/www/symfony

# Set working directory
WORKDIR /var/www/symfony

# Ensure permissions are correctly set
RUN chmod +x bin/console
