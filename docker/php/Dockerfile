FROM php:8.1-fpm

RUN apt-get update \
    && apt-get install -y \
    libmagickwand-dev \
    libmagickcore-dev \
    libzip-dev \
    libwebp-dev \
    wget \
    openssl

RUN docker-php-ext-install exif \
 && PHP_OPENSSL=yes \
 && docker-php-ext-install xml \
 && docker-php-ext-install filter \
 && docker-php-ext-install zip \
 && docker-php-ext-install bcmath \
 && docker-php-ext-install pdo_mysql \
 && docker-php-ext-install gd \
 && docker-php-ext-install intl

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www
