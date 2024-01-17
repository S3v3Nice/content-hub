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

# Set the uid, gid and home directory of the www-data user to default values
RUN usermod  --uid 1000 www-data && \
    groupmod --gid 1000 www-data && \
    usermod -d /home/www-data www-data && \
    mkdir /home/www-data && \
    chown -R www-data:www-data /home/www-data

USER www-data
WORKDIR /var/www
