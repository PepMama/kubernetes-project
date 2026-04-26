FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader

COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN docker-php-ext-install pdo pdo_mysql \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
    && a2enmod rewrite

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor

RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var

EXPOSE 80
