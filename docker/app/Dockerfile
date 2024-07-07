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
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Clone APFD extension repository
RUN git clone https://github.com/m6w6/ext-apfd.git /usr/src/php/ext/apfd

# Build and install APFD extension
RUN cd /usr/src/php/ext/apfd && \
    phpize && \
    ./configure && \
    make && \
    make install && \
    docker-php-ext-enable apfd

# Clean up
RUN rm -rf /usr/src/php/ext/apfd && \
    apt-get purge -y \
      git \
      autoconf \
      build-essential && \
    apt-get autoremove -y && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# Set the uid, gid and home directory of the www-data user to default values
RUN usermod  --uid 1000 www-data && \
    groupmod --gid 1000 www-data && \
    usermod -d /home/www-data www-data && \
    mkdir /home/www-data && \
    chown -R www-data:www-data /home/www-data

USER www-data
WORKDIR /var/www
