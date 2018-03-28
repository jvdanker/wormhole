FROM brettt89/silverstripe-web:7.1-platform

RUN apt-get update && apt-get install -y \
    unzip \
    zip

WORKDIR /var/www/html
ADD src/composer.json .
ADD src/composer.lock .

RUN composer install --prefer-dist

ADD src/Chat.php src/
ADD server.php .

RUN composer dump-autoload

EXPOSE 8080

CMD ["/usr/local/bin/php", "/var/www/html/server.php"]