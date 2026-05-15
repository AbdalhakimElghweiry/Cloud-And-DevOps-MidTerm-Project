# Start from official PHP image with Apache web server built in
# apache handles incoming HTTP requests for us — no extra setup needed
FROM php:8.2-apache

# Install the phpredis extension so PHP can talk to Redis
# docker-php-ext-install is a helper script included in the official PHP image
RUN pecl install redis && docker-php-ext-enable redis

# Copy all our PHP files into Apache's web root inside the container
COPY . /var/www/html/

# Tell Docker this container listens on port 80 (Apache's default)
EXPOSE 80