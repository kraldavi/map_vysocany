FROM php:8.4-apache

# PHP rozšíření pro MySQL
RUN docker-php-ext-install pdo pdo_mysql

# povolit mod_rewrite (Nette ho potřebuje)
RUN a2enmod rewrite

# změna DocumentRoot na /var/www/html/www
ENV APACHE_DOCUMENT_ROOT=/var/www/html/www

# přepsání konfigurace Apache, aby seděl nový DocumentRoot
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf
