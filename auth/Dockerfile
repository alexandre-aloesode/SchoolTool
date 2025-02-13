# Utilisation de PHP 7.3 avec Apache sur plateforme x86-64
FROM --platform=linux/x86-64 php:7.3-apache

# Mise à jour des paquets et installation des extensions nécessaires
RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y libzip-dev unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql zip \
    && a2enmod rewrite

# Définition des permissions correctes
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exposition du port Apache
EXPOSE 80

# Commande de démarrage
CMD ["apache2-foreground"]
