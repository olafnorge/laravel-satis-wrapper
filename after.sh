#!/usr/bin/env bash

# update apt cache
#sudo apt-get update

# install packages required for npm
#sudo apt-get install -y --no-install-recommends \
#    nasm \
#    pkg-config

# remove node_modules, install packages based on package.json, and compile assets
(cd code && rm -rf node_modules && npm install && npm run dev)
