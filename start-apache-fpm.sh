#!/bin/bash
# Start PHP-FPM
php-fpm &

# Start Apache in the foreground
apachectl -D FOREGROUND