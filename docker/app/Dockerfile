FROM php:8.1-fpm

# Install all necessary packets and extensions
RUN \
    apt-get update && \
    apt-get install -y \
      apt-utils \
      libpq-dev \
      libpng-dev \
      libzip-dev \
      zip  \
      unzip \
      npm  \
      git && \
    npm install -g  \
      yarn && \
    pecl config-set php_ini "${PHP_INI_DIR}/php.ini" && \
    docker-php-ext-install  \
      pdo_mysql \
      bcmath \
      gd \
      zip && \
    pecl install  \
      apfd && \
    docker-php-ext-enable  \
      apfd && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

# Change the uid and gid of the www-data user to default values
RUN usermod  --uid 1000 www-data
RUN groupmod --gid 1000 www-data

USER www-data
WORKDIR /var/www
