#!/bin/bash

# Help message
if [[ $1 == "help" || -z $1 ]]; then
    echo "Available tasks:"
    echo "  setup: Install dependencies"
    echo "  test: Run PHPUnit tests"
    echo "  lint: Lint PHP files"
    echo "  deploy: Deploy application"
    exit 0
fi

# Install dependencies
if [[ $1 == "setup" ]]; then
    composer install
fi

# Run PHPUnit tests
if [[ $1 == "test" ]]; then
    vendor/bin/phpunit
fi

# Lint PHP files
if [[ $1 == "lint" ]]; then
    find . -name '*.php' | xargs -n1 php -l
fi

# Deploy application
if [[ $1 == "deploy" ]]; then
    rsync -avz --exclude='.git/' ./ user@production-server:/var/www/html/
fi