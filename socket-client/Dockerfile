FROM brettt89/silverstripe-web:7.1-platform

RUN mkdir /uploads && chgrp www-data:1000 /uploads
WORKDIR /var/www/html
COPY html/ ./html/
COPY src/ ./src/
COPY .htaccess .

EXPOSE 80