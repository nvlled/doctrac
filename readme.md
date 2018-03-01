# doctrac

PSU document tracking system

## Building and running locally
(instructions apply to Ubuntu and Debian systems, adapt instructions to other OS as needed)

0. Install php7, php-composer, mysql

1. Install package dependencies listed on `package-dependencies.txt`, e.g.
```
sudo apt install php-domstring php-xml
```

2. Run ```composer update``` to install php vendor dependencies required by laravel

3. Copy the file ```.env.example``` to ```.env```

4. Edit the file ```.env``` according to your mysql setup. Particularly, edit the fields DB_DATABASE, DB_USERNAME, and DB_PASSWORD.

5. Run ```php artisan key:generate```

6. Run ```php artisan migrate:fresh```

7. Run ```php artisan serve```

