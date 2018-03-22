#!/bin/bash

#docker run -it -p 8080:80 -v "$PWD/html":/var/www/html php:5.6-apache
docker run -p 8080:80 -v "$PWD/html":/var/www/html brettt89/silverstripe-web

