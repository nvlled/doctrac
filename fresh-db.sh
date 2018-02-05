#!/bin/sh
php artisan migrate:fresh
mysql doctrac < db.sql
