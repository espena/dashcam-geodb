FROM php:8.2-rc-cli
RUN apt-get update && apt-get install -y libzip-dev
RUN docker-php-ext-install zip
CMD tail -f /dev/null
