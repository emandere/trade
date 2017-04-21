FROM php:7.0-apache
COPY config/php.ini /usr/local/etc/php/
COPY orders.php /var/www/html/trade/orders.php
COPY index.html /var/www/html/trade/index.html
