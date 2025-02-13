FROM  --platform=linux/x86-64 php:7.3-apache
RUN apt-get update && apt-get upgrade -y
RUN docker-php-ext-install mysqli


RUN a2enmod rewrite

RUN docker-php-ext-install pdo pdo_mysql
EXPOSE 80