FROM php:8.3-apache

# Installation des extensions PDO MySQL nécessaires
RUN docker-php-ext-install pdo pdo_mysql

# Activation des modules Apache
RUN a2enmod rewrite headers

# Configuration PHP pour développement
RUN docker-php-ext-enable opcache
RUN echo "error_log = /var/log/apache2/php_error.log" >> /usr/local/etc/php/conf.d/docker-php.ini
RUN echo "display_errors = Off" >> /usr/local/etc/php/conf.d/docker-php.ini
RUN echo "log_errors = On" >> /usr/local/etc/php/conf.d/docker-php.ini

# Modification du DocumentRoot vers /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permissions pour les logs
RUN touch /var/log/apache2/php_error.log && chmod 666 /var/log/apache2/php_error.log