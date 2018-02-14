#!/bin/sh
php artisan migrate:fresh
mysql doctrac -p < db.sql
