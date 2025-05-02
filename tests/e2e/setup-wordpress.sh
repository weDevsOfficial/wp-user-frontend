#!/bin/bash

# Start Docker containers
docker-compose up -d

# Wait for WordPress to be fully ready
echo "Waiting for WordPress to start..."
sleep 20

# Install WP-CLI if not already installed
if ! command -v wp &> /dev/null; then
  curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x wp-cli.phar
  sudo mv wp-cli.phar /usr/local/bin/wp
fi

# Get container ID of the WordPress service
CONTAINER_ID=$(docker ps -qf "ancestor=wordpress:6.4-php7.4-apache")

# Run WP install inside the container
docker exec -u www-data "$CONTAINER_ID" wp core install \
  --url=http://localhost:8888 \
  --title=\"Test Site\" \
  --admin_user=admin \
  --admin_password=admin \
  --admin_email=admin@example.com \
  --skip-email
