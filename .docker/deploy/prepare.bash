#!/usr/bin/env bash

echo "export COMPOSE_PROJECT_NAME=${COMPOSE_PROJECT_NAME}"

docker login -u gitlab-ci-token -p $CI_BUILD_TOKEN registry.gitlab.com

mkdir .docker/tmp/artifacts

external_links=()
depends_on=()

if [[ -z "$(docker ps --all | grep $COMPOSE_PROJECT_NAME)" ]]; then
  export REVIEW_RECREATE="true"
fi

if [ "$REVIEW_RECREATE" = "true" ]; then
  bash .docker/deploy/clean.bash
fi

if [[ -f docker-compose.review.override.yml ]] ; then
    exit
fi

echo "Create docker-compose.review.override.yml"
cat > docker-compose.review.override.yml <<EOF
version: '3.7'
services:
EOF

if [ "$REVIEW_USE_DATABASE_IMAGE" = "true" ]; then
  depends_on+=('mysql')
cat >> docker-compose.review.override.yml <<EOF
    mysql:
        image: registry.gitlab.com/icemint-skyler-lucci/heytutor/database:latest
        command:
            - "--default-authentication-plugin=mysql_native_password"
        networks:
            - private
        environment:
            MYSQL_ROOT_PASSWORD: db
            MYSQL_DATABASE: db
            MYSQL_USER: db
            MYSQL_PASSWORD: db
EOF
else
  external_links+=('elasticsearchHeytutorRc:elasticsearch')
fi

if [ "$REVIEW_USE_ELASTICSEARCH_IMAGE" = "true" ]; then
  export REVIEW_ELASTICSEARCH_REINDEX="true"
  depends_on+=('elasticsearch')
cat >> docker-compose.review.override.yml <<EOF
    elasticsearch:
        image: registry.gitlab.com/icemint-skyler-lucci/heytutor/elasticsearch:latest
        networks:
            - private
        environment:
            - ES_JAVA_OPTS=-Xmx1g -Xms1g
EOF
else
  external_links+=('elasticsearchHeytutorRc:elasticsearch')
fi

external_links_text=""
depends_on_text=""

for i in ${external_links[@]}; do
  external_links_text=$(cat <<EOF
            - ${i}
EOF
)
done

for i in ${depends_on[@]}; do
  depends_on_text=$(cat <<EOF
            - ${i}
EOF
)
done

if [ "${#depends_on[@]}" -eq 0 ]; then
    depends_on_text="[]"
fi

if [[ -n "$external_links_text" ]]; then
cat >> docker-compose.review.override.yml <<EOF
    php:
        depends_on:
${depends_on_text}
        external_links:
${external_links_text}

    queue:
        depends_on:
${depends_on_text}
        external_links:
${external_links_text}
EOF
fi

if [ "$REVIEW_RECREATE" = "true" ]; then
  echo "Pull images if no conteiners (first run)."
  docker-compose -f docker-compose.review.yml -f docker-compose.review.override.yml pull
fi
