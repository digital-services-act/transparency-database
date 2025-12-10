FROM php:8.3-fpm-alpine

ENV MEMCACHED_DEPS zlib-dev libmemcached-dev cyrus-sasl-dev
RUN docker-php-ext-install bcmath pdo_mysql
RUN apk upgrade --update && apk add --no-cache \
    linux-headers \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    memcached \
    git \
    zlib-dev \
    libmemcached-libs \
    zlib \
    autoconf \
    g++ \
    make \
    zsh \
    ca-certificates \
    icu-dev \
    icu-libs \
    icu-data-full \
    gcc \
    libc-dev
RUN update-ca-certificates
RUN set -xe \
    && apk add --no-cache --update --virtual .phpize-deps $PHPIZE_DEPS \
    && apk add --no-cache --update --virtual .memcached-deps $MEMCACHED_DEPS \
    && pecl install memcached \
    && pecl install pcov \
    && echo "extension=pcov" >> /usr/local/etc/php/conf.d/30_pcov.ini \
    && echo "extension=memcached.so" >> /usr/local/etc/php/conf.d/20_memcached.ini \
    && rm -rf /usr/share/php8 \
    && rm -rf /tmp/* \
    && apk del .memcached-deps .phpize-deps \
    && rm -rf /var/cache/apk/*
RUN docker-php-ext-install gd zip intl
RUN pecl install -o -f redis && rm -rf /tmp/pear
RUN docker-php-ext-enable redis
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /bin/composer
RUN echo 'memory_limit = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini;
RUN sh -c "$(curl -fsSL https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"

# Configure Xdebug
RUN echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.log=/var/log/xdebug.log" >> /usr/local/etc/php/conf.d/xdebug.ini

# Create log file and set permissions
RUN touch /var/log/xdebug.log && chown 1000:1000 /var/log/xdebug.log

COPY ./ecas-certs/* /etc/ssl/certs/

CMD ["php-fpm"]

EXPOSE 9000
