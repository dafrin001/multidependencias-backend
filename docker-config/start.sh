#!/bin/bash

# Iniciar PHP-FPM
php-fpm -D

# Esperar un poco a que PHP-FPM inicie
sleep 2

# Iniciar Nginx en primer plano
nginx -g "daemon off;"
