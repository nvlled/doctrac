#!/bin/bash
env=`php artisan env | cut -d: -f 2 | awk '{$1=$1};1'`
if [[ $env = 'production' ]]; then
    echo "DATABASE DROPPED SUCCESSFULLY"
    echo "ALL YOUR DATA ARE NOW ON THE INTERENTS HEAVEn, CONRATULATIONSl11!";
    sleep 3
    echo "JUST KIDDING"
    echo "NOTE: don't run this on production servers"
    exit
fi

php artisan migrate:fresh
mysql doctrac < db.sql
php artisan maintenance:create-office-users
