FROM php:7.2-fpm
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apt-get update && apt-get install -y \
        libpq-dev \
        libxml2-dev \
        git \
        zip
RUN docker-php-ext-install -j$(nproc) soap \
    && docker-php-ext-install -j$(nproc) pgsql \
    && docker-php-ext-install -j$(nproc) pdo_pgsql
WORKDIR /app
COPY . /app/
VOLUME /app
RUN composer install --no-dev --no-interaction
