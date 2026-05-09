FROM php:8.3-fpm-alpine

ENV PHPIZE_DEPS="autoconf file g++ gcc libc-dev make pkgconf re2c"

RUN mkdir -p /var/www/html

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN sed -i "s/user = www-data/user = root/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/group = www-data/group = root/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/g" /usr/local/etc/php-fpm.d/www.conf
RUN echo "php_admin_flag[log_errors] = on" >> /usr/local/etc/php-fpm.d/www.conf

# Install intl extension (needed for Laravel Number formatting) and pdftotext for layout-aware PDF extraction
RUN apk add --no-cache icu-libs poppler-utils
RUN apk add --no-cache --virtual .build-intl \
    $PHPIZE_DEPS \
    icu-dev \
    g++ \
    zlib-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl \
    && apk del .build-intl

RUN apk add --no-cache freetype libjpeg-turbo libpng \
    && apk add --no-cache --virtual .build-core-exts \
        $PHPIZE_DEPS \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-core-exts
    

RUN echo "upload_max_filesize = 50M" > /usr/local/etc/php/conf.d/uploads.ini
RUN echo "post_max_size = 60M" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

USER root
CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]
