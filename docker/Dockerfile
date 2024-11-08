FROM php:8.3-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN apt-get update && apt-get install -y

COPY ./ /var/www/html
