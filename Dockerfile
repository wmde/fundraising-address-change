FROM php:7.4-cli as app

RUN apt-get update \
	&& docker-php-ext-install -j$(nproc) pdo pdo_mysql

FROM app as app_debug

RUN pecl install xdebug-2.9.4 \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini
