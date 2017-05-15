FROM php:7.1-fpm
RUN apt-get install libcurl4-openssl-dev pkg-config
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

ADD * /var/www/html/trade/