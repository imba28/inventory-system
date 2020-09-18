FROM php:7.4-apache

EXPOSE 80
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

COPY .docker/docker.conf /etc/apache2/conf-available/docker-php.conf
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y libxslt-dev git unzip libzip-dev && rm -rf /var/cache/apt
RUN docker-php-ext-install xsl zip pdo pdo_mysql

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

WORKDIR /var/www/html
USER www-data
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-autoloader --no-scripts --no-cache
COPY --chown=www-data:www-data . .
RUN composer dump-autoload --optimize
USER root

