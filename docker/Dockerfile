FROM php:8.2-apache

# Installation des extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Activation du module rewrite d'Apache
RUN a2enmod rewrite

# Configuration Apache
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Configuration PHP
RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini

# Création des répertoires de travail
RUN mkdir -p /var/www/html/public/uploads/articles \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Définition du répertoire de travail
WORKDIR /var/www/html

# Exposition du port 80
EXPOSE 80
