FROM php:8.3-fpm-alpine3.17 as runtime
LABEL maintainer="Alexey Perevoshchikov <netgear155@gmail.com>"

ENV \
    COMPOSER_HOME="/tmp/composer" \
    PS1='\[\033[1;32m\]\[\033[1;36m\][\u@\h] \[\033[1;34m\]\w\[\033[0;35m\] \[\033[1;36m\]# \[\033[0m\]' \
    PHP_IDE_CONFIG='serverName=crossroad' \
    HOME='/tmp'

ENV PHP_EXTENSIONS \
    mbstring \
    pdo \
    pdo_pgsql \
    json

COPY --from=composer:2.7.2 /usr/bin/composer /usr/bin/composer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions ${PHP_EXTENSIONS}

WORKDIR /var/www/app
USER www-data

RUN docker-php-source delete

CMD ["php-fpm"]

EXPOSE 9000 9001
