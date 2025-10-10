FROM php:8.3-fpm-alpine

ENV PHPIZE_DEPS="autoconf file g++ gcc libc-dev make pkgconf re2c"

ARG UID
ARG GID

ENV UID=${UID}
ENV GID=${GID}

RUN mkdir -p /var/www/html

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# MacOS staff group's gid is 20, so is the dialout group in alpine linux. We're not using it, let's just remove it.
RUN delgroup dialout

RUN addgroup -g ${GID} --system laravel
RUN adduser -G laravel --system -D -s /bin/sh -u ${UID} laravel

RUN sed -i "s/user = www-data/user = laravel/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/group = www-data/group = laravel/g" /usr/local/etc/php-fpm.d/www.conf
RUN echo "php_admin_flag[log_errors] = on" >> /usr/local/etc/php-fpm.d/www.conf


# Install intl extension (needed for Laravel Number formatting)
RUN apk add --no-cache icu-libs
RUN apk add --no-cache --virtual .build-intl \
    $PHPIZE_DEPS \
    icu-dev \
    g++ \
    zlib-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl \
    && apk del .build-intl

RUN apk add --no-cache $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_mysql pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

USER laravel

CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]
