FROM brettt89/silverstripe-web:7.1-platform

RUN apt-get update && apt-get install -y \
    unzip \
    zip

RUN mkdir /uploads && chown www-data:www-data /uploads

#USER www-data

WORKDIR /var/www/html
ADD composer.json .
ADD composer.lock .
COPY html/ ./html/
COPY src/ ./src/
COPY .htaccess .

RUN composer install

EXPOSE 80