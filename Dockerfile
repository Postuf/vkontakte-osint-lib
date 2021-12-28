FROM php:8.1-cli

LABEL version="1.0.0"
LABEL description="Postuf Vkontakte OSINT Library"

RUN apt-get update

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /usr/local/bin/composer

COPY examples /app/examples/
COPY src /app/src/
COPY composer.json /app/
COPY composer.lock /app/

WORKDIR /app

RUN composer update
