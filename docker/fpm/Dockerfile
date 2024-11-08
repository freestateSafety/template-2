FROM php:fpm
RUN apt-get update && apt-get install -y libzip-dev libxml2-dev libonig-dev libsodium-dev
RUN apt-get clean && rm -Rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_mysql mysqli mbstring zip dom intl sodium
# Install xdebug - need 3.1 to support php-7.2
RUN pecl install xdebug && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini
ADD core.ini /usr/local/etc/php/conf.d/core.ini
RUN curl -ksS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --2.2

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

USER www
WORKDIR /var/www
