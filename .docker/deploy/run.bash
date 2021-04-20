#!/usr/bin/env bash

docker-compose-run () {
  docker-compose -f docker-compose.review.yml -f docker-compose.review.override.yml "$@"
}

docker-compose-run stop
docker-compose-run up -d --no-recreate --remove-orphans
echo 'Waiting for mysql...' && sleep 10 && echo 'DB is ready. No eto ne tohno (c)'

docker-compose-run exec -T php composer install -o
docker-compose-run exec -T php ./init --env=Staging --overwrite=y
docker-compose-run exec -T php php yii migrate --interactive=0

docker-compose-run exec -T php php yii cache/flush-all
docker-compose-run exec -T php php yii cache/flush-schema --interactive=0

docker-compose-run up -d --no-recreate queue

echo "Build themes"
docker-compose-run exec -T php bash -c 'cd themes/basic && yarn install'
docker-compose-run exec -T php bash -c 'cd themes/basic && node_modules/gulp/bin/gulp.js --cwd ./frontend/assets/ build'
docker-compose-run exec -T php bash -c 'cd themes/basic && node_modules/gulp/bin/gulp.js --cwd ./mobile/assets/ build'
docker-compose-run exec -T php bash -c 'cd themes/basic && node_modules/gulp/bin/gulp.js --cwd ./backend/assets/ build'
docker-compose-run exec -T php bash -c 'cd modules/chat/assets/src && sh ./build.sh'

docker-compose-run exec -T php ./vendor/bin/swagger --output api/documentation/swagger ./api/controllers ./modules/*/controllers/api
docker-compose-run exec -T php ./vendor/bin/swagger --output api2/documentation/swagger ./modules/*/models/api2 ./api2/controllers ./modules/*/controllers/api2

docker-compose-run ps
docker-compose-run logs > ".docker/tmp/artifacts/docker-${CI_BUILD_REF}.log"

#if [ "$REVIEW_RECREATE" = "true" ]; then
#  docker-compose-run exec -T php bash -c 'yes yes | php yii account/elasticsearch/recreate-all'
#fi
