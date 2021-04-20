#!/usr/bin/env bash

echo "Create .env"
cp /home/gitlab-runner/www/heytutor/rc/api/.env ./.env
sed --in-place 's/FRONTEND_URL=.*/FRONTEND_URL=\"https:\/\/'"${CI_BUILD_REF_SLUG}"'_'"${CI_PROJECT_PATH_SLUG}"'.review.eltex.dev\/"/' .env
sed --in-place 's/BACKEND_URL=.*/BACKEND_URL=\"https:\/\/'"${CI_BUILD_REF_SLUG}"'_'"${CI_PROJECT_PATH_SLUG}"'.review.eltex.dev\/backend\/"/' .env
sed --in-place 's/DB_HOST=.*/DB_HOST="mysql"/' .env
sed --in-place 's/DB_NAME=.*/DB_NAME="db"/' .env
sed --in-place 's/DB_USERNAME=.*/DB_USERNAME="db"/' .env
sed --in-place 's/DB_PASSWORD=.*/DB_PASSWORD="db"/' .env
sed --in-place 's/SMTP_TITLE=.*/SMTP_TITLE='"${COMPOSE_PROJECT_NAME}"'/' .env
sed --in-place 's/ELASTICSEARCH_ADDRESS=.*/ELASTICSEARCH_ADDRESS="elasticsearch:9200"/' .env

echo "Create volume and copy data"
CONTEINER_DUMMY_NAME="${COMPOSE_PROJECT_NAME}_dummy"
docker volume create "${COMPOSE_PROJECT_NAME}_volume"
docker rm "${CONTEINER_DUMMY_NAME}"
docker create --name "${CONTEINER_DUMMY_NAME}" -v "${COMPOSE_PROJECT_NAME}_volume":/data ubuntu:latest tail -f /dev/null
docker cp ./ "${CONTEINER_DUMMY_NAME}":/data/
docker start "${CONTEINER_DUMMY_NAME}"
docker exec -t --privileged "${CONTEINER_DUMMY_NAME}" chown -R www-data:www-data /data
docker stop "${CONTEINER_DUMMY_NAME}"
docker rm "${CONTEINER_DUMMY_NAME}"
