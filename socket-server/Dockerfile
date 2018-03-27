FROM brettt89/silverstripe-web:7.1-platform

RUN apt-get update && apt-get install -y \
    unzip \
    zip

WORKDIR /var/www/html
ADD src/Chat.php src/
ADD server.php .
ADD index.html .
ADD composer.json .

RUN composer install --prefer-dist
