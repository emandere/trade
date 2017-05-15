FROM php:7.1-fpm
RUN apt-get update
RUN apt-get install -y autoconf pkg-config libssl-dev
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

ADD * /var/www/html/trade/