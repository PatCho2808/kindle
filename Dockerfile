FROM thecodingmachine/php:7.2-v3-apache

USER root

COPY /app /var/www/html

WORKDIR /var/www/html

RUN apt-get update

ENV APP_ENV=dev
ENV TERM=xterm

USER docker