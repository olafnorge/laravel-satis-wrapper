#!/usr/bin/env bash

# install composer dependencies
echo "INSTALL COMPOSER DEPENDENCIES"
composer install \
    --no-autoloader \
    --no-scripts \
    --no-progress \
    --no-suggest \
    --no-ansi \
    --no-interaction \
    --no-plugins \
    --working-dir=/home/vagrant/code

# setup app
echo "INSTALL YARN DEPENDENCIES"
(cd /home/vagrant/code && yarn install \
        --ignore-optional \
        --no-progress \
        --non-interactive)
echo "BUILD ASSETS"
(cd /home/vagrant/code && npm run development -- --color=false --display=minimal --no-progress --bail)
