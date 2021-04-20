#!/usr/bin/env bash

rm docker-compose.review.override.yml
echo "Stop and clear docker conteiners and remove volume."
docker ps -a | grep "${COMPOSE_PROJECT_NAME}" | awk '{print $1}' | xargs -I {} docker rm --force {}

docker volume rm --force "${COMPOSE_PROJECT_NAME}_volume"
