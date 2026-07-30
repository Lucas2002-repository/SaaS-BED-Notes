# syntax=docker/dockerfile:1

# For college approval, replace the tag with the approved immutable digest:
# FROM php:8.4-fpm-bookworm@sha256:<approved-digest>
FROM php:8.4-fpm-bookworm

ARG MONGODB_EXTENSION_VERSION=2.1.1

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libcurl4-openssl-dev \
        libicu-dev \
        libonig-dev \
        libssl-dev \
        libxml2-dev \
        libzip-dev \
        pkg-config \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo \
        zip \
    && pecl install "mongodb-${MONGODB_EXTENSION_VERSION}" \
    && docker-php-ext-enable mongodb \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

# Pin the Composer image by digest after IT approval.
COPY --from=composer:2.10.2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 9000

CMD ["php-fpm"]
