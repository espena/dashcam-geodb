FROM php:8.2-rc-cli
RUN apt-get update && apt-get install -y libzip-dev postgresql-server-dev-all exiftool
RUN docker-php-ext-install zip pgsql
RUN docker-php-ext-install pdo_pgsql
CMD tail -f /dev/null
