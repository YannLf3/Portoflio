FROM php:8.3-apache

# Installation des extensions PDO MySQL nécessaires
RUN docker-php-ext-install pdo pdo_mysql

# Activation du module rewrite pour le routage MVC
RUN a2enmod rewrite

# Modification du DocumentRoot vers /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf