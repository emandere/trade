FROM php:7.0-apache
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

ADD * /var/www/html/trade/