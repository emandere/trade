FROM php:7.1-fpm
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

ADD * /var/www/html/trade/