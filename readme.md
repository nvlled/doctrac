# doctrac


PSU document tracking system

## issues, feedback, and suggestions
[submit an issue or feature request](https://github.com/nvlled/doctrac/issues)

[developer chat/messaging](https://gitter.im/doctrac/Lobby) [![Join the chat at https://gitter.im/doctrac/Lobby](https://badges.gitter.im/doctrac/Lobby.svg)](https://gitter.im/doctrac/Lobby?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)


## Building and running locally
(instructions apply to Ubuntu and Debian systems, adapt instructions to other OS as needed)

0. Install php7, php composer, mysql, git

1. Install package dependencies listed on `apt-dependencies.sh`, e.g.
```
sudo apt install php-domstring php-xml php-mysql
```

2. Run ```composer update``` to install php vendor dependencies required by laravel

3. Copy the file ```.env.example``` to ```.env```

4. Edit the file ```.env``` according to your mysql setup. Particularly, edit the fields DB_DATABASE, DB_USERNAME, and DB_PASSWORD.

5. Run ```php artisan key:generate```

6. Run ```php artisan migrate:fresh```

7. Run ```php artisan serve```

### Optional dependencies
1. install redis-server and laravel-echo-server
2. edit ```.env```, set BROADCAST_DRIVER and QUEUE_DRIVER to redis
3. run the worker: ```php artisan queue:work```
   supervisor can also be used to daemonize the work queue
4. setup and run the echo server: 
```
laravel-echo-server init
laravel-echo-server client:add
(edit the generated file `laravel-echo-server.json`, e.g. authHost)
laravel-echo-server start
```
similarly, use supervisor to daemonize the echo server
