FROM php:8.2-apache

# Installation de l'extension pdo_mysql pour PHP
RUN docker-php-ext-install pdo pdo_mysql

# Activation du module rewrite d'Apache
RUN a2enmod rewrite

# Copie des fichiers du projet dans le répertoire web d'Apache
COPY . /var/www/html/

# Permissions pour Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
