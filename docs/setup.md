##Work begin:
####Docker
Read about docker (docker compose) if you have not encountered it before.
Install docker

####Clone the project
```
git clone git@gitlab.com:eltex/winitclinic/winitclinic-api.git
git checkout your_branch
```

####Setup local environment
- Run `sudo bash ./hosts.bash` to add host names to `/etc/hosts` file
- Copy `.env.example` to `.env`
- Configure `.env` external services
- Configure xdebug (disabled by default). For linux change `XDEBUG_CONFIG=remote_host=docker.for.mac.localhost` to `XDEBUG_CONFIG=remote_host=172.17.0.1`

**All command must be run under the docker container**

We have script **after_update.sh** to re-init environment after code update or on the first run.

## Before run docker:
```
docker login registry.gitlab.com
docker-compose up -d
```

##Commands
####Run init 
```
docker-compose exec php php init
```

####Run composer:
```
docker-compose exec php composer install
```

####Run Yii2 migrations in php container
```
docker-compose exec php php yii migrate
```

####Run gulp on php container
[link](https://gitlab.com/eltex/winitclinic/winitclinic-api/blob/master/themes/basic/README.md)

####Run bash on php container
```
docker-compose exec php bash
```


You can connect to mysql from the local machine `127.0.0.1:3306` (like tool in phpstorm)

Containers list:
- **php** - main project. PHP 7.2
- **nginx** - web server on port 80
- **mysql** - mysql 8.0
- **elasticsearch** - Elasticsearch 6.2
- **phpmyadmin** - PhpMyAdmin on port 81 (**Disabled by default**)
- **adminer** - Adminer on port 82
