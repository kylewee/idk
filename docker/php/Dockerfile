# PHP-FPM 8.3 with extensions needed for Rukovoditel and Twilio handlers
FROM php:8.3-fpm

# Install dependencies and PHP extensions
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
      libpng-dev libjpeg-dev libfreetype6-dev \
      libzip-dev zip \
      libonig-dev \
      libxml2-dev \
      libcurl4-openssl-dev \
      libicu-dev \
      git unzip; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
      gd \
      mysqli \
      pdo \
      pdo_mysql \
      mbstring \
      zip \
      exif \
      intl \
      curl \
      xml; \
    docker-php-ext-enable opcache; \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Optional: set recommended PHP.ini settings here if needed
# COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
