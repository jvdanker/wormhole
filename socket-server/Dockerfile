FROM brettt89/silverstripe-web:7.1-platform

RUN apt-get update && apt-get install -y \
    unzip \
    zip

WORKDIR /var/www/html
ADD src/Chat.php src/
ADD server.php .
ADD composer.json .

RUN composer install --prefer-dist

CMD ["/usr/local/bin/php", "/var/www/html/server.php"]