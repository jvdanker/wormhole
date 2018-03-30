FROM brettt89/silverstripe-web:7.1-platform

RUN apt-get update && apt-get install -y \
    unzip \
    zip

USER www-data

WORKDIR /var/www/html
ADD composer.json .
ADD composer.lock .
COPY src/ ./src/

RUN composer install

EXPOSE 8080

CMD ["/usr/local/bin/php", "/var/www/html/src/server.php"]