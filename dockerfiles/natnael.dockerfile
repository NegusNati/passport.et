FROM nginx:stable-alpine

ARG UID
ARG GID

ENV UID=${UID}
ENV GID=${GID}

RUN delgroup dialout

RUN addgroup -g ${GID} --system laravel
RUN adduser -G laravel --system -D -s /bin/sh -u ${UID} laravel
RUN sed -i "s/user  nginx/user laravel/g" /etc/nginx/nginx.conf

ADD ./nginx/natnael.conf /etc/nginx/conf.d/

RUN mkdir -p /var/www/html

RUN chown -R laravel:laravel /var/www/html
RUN chmod -R 755 /var/www/html