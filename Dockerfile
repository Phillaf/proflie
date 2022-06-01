FROM phpswoole/swoole:latest as dev
 
RUN docker-php-ext-install mysqli pdo_mysql

FROM dev as build

COPY ./bootstrap /var/www/bootstrap
COPY ./mysql-migration /var/www/mysql-migration
COPY ./src /var/www/src
COPY ./composer.json /var/www/composer.json
COPY ./migration.php /var/www/migration.php
COPY ./server.php /var/www/server.php

RUN composer update
