FROM thecodingmachine/php:7.4-v3-apache-node12

USER root

COPY /app /var/www/html

WORKDIR /var/www/html

RUN apt-get update

ENV APP_ENV=dev
ENV TERM=xterm

USER docker