#!/usr/bin/env bash

cd "$( dirname "$0" )"
yarn install
#bower install
node_modules/.bin/grunt build --env=prod
node_modules/.bin/grunt build --env=prod --target=mobile
