#!/usr/bin/env bash

git checkout master
git pull origin master

#if [[ `git status --porcelain` ]]; then echo -e "\033[0;31mYou have modified files in git\033[0m"; exit 1; fi

php -r "function_exists('opcache_reset') ? opcache_reset() : null;"

# Remove then upgrade php on production
composer install --ignore-platform-reqs -o
php init --env=Production --overwrite=y
php yii migrate --interactive=0
php yii cache/flush-all
php yii cache/flush-schema --interactive=0

supervisorctl restart queue
supervisorctl restart queue_yii:

cd themes/basic
yarn install
node_modules/gulp/bin/gulp.js --cwd ./common/assets/ js libs images --env production
node_modules/gulp/bin/gulp.js --cwd ./backend/assets/ build --env production

#curl -XDELETE "https://api.cloudflare.com/client/v4/zones/6a7bdd79e869efb3680e1e075717fbc6/purge_cache" -H "Content-Type:application/json" -H "X-Auth-Email:noreply@winitclinic.com" -H "X-Auth-Key:24ff963cfa7ca8f0f41a042e8f34bc597d754" --data '{"purge_everything":true}'
#curl -XDELETE "https://api.cloudflare.com/client/v4/zones/4fce60674246188459148bb333464cc0/purge_cache" -H "Content-Type:application/json" -H "X-Auth-Email:noreply@winitclinic.com" -H "X-Auth-Key:24ff963cfa7ca8f0f41a042e8f34bc597d754" --data '{"purge_everything":true}'
