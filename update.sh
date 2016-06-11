#!/usr/bin/env sh

#
# File: update.sh
# Authors: Scott Kidder, Clayton Smith, Colin Begg
# Purpose: This script will re-configure an hsmm-pi node Raspberry Pi running
#   Raspbian Jessie (or Jessie Lite) with the updated HSMM-Pi components.
#

if [ "$(id -u)" = "0" ]
  then echo "Please do not run as root, HTTP interface will not work"
  exit
fi

PROJECT_HOME=${HOME}/app-config

# Pull in the udpate from GitHub
cd ${PROJECT_HOME}
git pull origin master

# Set symlink to webapp
if [ -d /var/www/html ]; then
    cd /var/www/html
else
    cd /var/www
fi
sudo rm -f index.html
sudo ln -s ${PROJECT_HOME}/src/var/www/index.html

cd ${PROJECT_HOME}/src/var/www/app-config
# Remove cache files
sudo rm -rf tmp
# Create temporary directory used by APP-CONFIG webapp, granting write priv's to www-data
mkdir -p tmp/cache/models
mkdir -p tmp/cache/persistent
mkdir -p tmp/logs
mkdir -p tmp/persistent
sudo chgrp -R www-data tmp
sudo chmod -R 775 tmp

# Remove old database
sudo rm -rf /var/data/app-config
# Create database
sudo mkdir -p /var/data/app-config
sudo chown root.www-data /var/data/app-config
sudo chmod 775 /var/data/app-config
if [ ! -e /var/data/app-config/app-config.sqlite ]; then
    sudo Console/cake schema create -y
    sudo chown root.www-data /var/data/app-config/app-config.sqlite
    sudo chmod 664 /var/data/app-config/app-config.sqlite
fi

# print success message if we make it this far
printf "\n\n---- SUCCESS ----\n\nLogin to the web console to re-configure the apps\n"
