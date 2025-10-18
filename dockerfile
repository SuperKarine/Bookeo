# syntax=docker/dockerfile:1

# Installe Composer + MongoDB pour composer install
FROM php:8.2-cli AS deps

WORKDIR /app

# Installe MongoDB + Composer 
RUN apt-get update && apt-get install -y \
    unzip curl git \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
 && pecl install mongodb-1.21.0 \
 && docker-php-ext-enable mongodb \
 && curl -sS https://getcomposer.org/installer | php \
 && mv composer.phar /usr/local/bin/composer \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction

COPY . /app

# Image finale avec Apache + MongoDB + Vendors
FROM php:8.2-apache AS final

# Installe extensions PHP requises
RUN apt-get update && apt-get install -y \
    unzip curl git \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
 && pecl install mongodb-1.21.0 \
 && docker-php-ext-enable mongodb \
 && docker-php-ext-install pdo pdo_mysql \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installe Composer
RUN curl -sS https://getcomposer.org/installer | php \
 && mv composer.phar /usr/local/bin/composer

# Config Apache
RUN a2enmod rewrite
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copie les vendors 
COPY --from=deps /app/vendor /var/www/html/vendor

# Copie le code
COPY ./public /var/www/html/public
COPY ./src /var/www/html/src
COPY ./config /var/www/html/config

# Apache custom config
COPY ./apache.conf /etc/apache2/conf-available/myapp.conf
RUN a2enconf myapp

# Définir le DocumentRoot vers /public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Sécurité 
USER www-data