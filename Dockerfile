FROM phpswoole/swoole:latest as dev
 
RUN docker-php-ext-install mysqli pdo_mysql

RUN apt-get update && apt-get install -y certbot

FROM dev as build

COPY ./bootstrap /var/www/bootstrap
COPY ./mysql-migration /var/www/mysql-migration
COPY ./src /var/www/src
COPY ./composer.json /var/www/composer.json
COPY ./migration.php /var/www/migration.php
COPY ./server.php /var/www/server.php
COPY ./letsencrypt /var/www/letsencrypt

COPY ./.docker/mock.pem /etc/letsencrypt/live/proflie.com/fullchain.pem
COPY ./.docker/mock-key.pem /etc/letsencrypt/live/proflie.com/privkey.pem

RUN composer update
