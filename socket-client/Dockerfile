FROM brettt89/silverstripe-web:7.1-platform

RUN apt-get update && apt-get install -y \
    unzip \
    zip

RUN mkdir /uploads && chown www-data:www-data /uploads
WORKDIR /var/www/html
COPY html/ ./html/
COPY src/ ./src/
COPY .htaccess .

EXPOSE 80