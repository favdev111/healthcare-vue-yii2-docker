#!/usr/bin/env bash

docker-compose exec php ./init --env=Development --overwrite=y
docker-compose exec php composer install -o
docker-compose exec php php ./yii migrate --interactive=0
if [ "$1" != "api" ]; then
    docker-compose exec php bash -c 'cd themes/basic && yarn install'
    docker-compose exec php bash -c 'cd themes/basic && node_modules/gulp/bin/gulp.js --cwd ./common/assets/ js images'
    docker-compose exec php bash -c 'cd themes/basic && node_modules/gulp/bin/gulp.js --cwd ./backend/assets/ build'
fi;
docker-compose exec php ./vendor/bin/openapi --output api/documentation/swagger ./api/controllers ./modules/*/controllers/api
docker-compose exec php ./vendor/bin/openapi --output api2/documentation/swagger ./api2/documentation ./modules/*/models/api2
#docker-compose exec php bash -c 'yes yes | php yii account/elasticsearch/recreate-all'
