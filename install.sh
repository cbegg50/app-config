#!/usr/bin/env sh

#
# File: install.sh
# Authors: Scott Kidder, Clayton Smith, Colin Begg
# Purpose: This script will configure a newly-imaged Raspberry Pi running
#   Raspbian Jessie Lite with the dependencies and appserver components.
#

if [ "$(id -u)" = "0" ]
  then echo "Please do not run as root, HTTP interface will not work"
  exit
fi

PROJECT_HOME=${HOME}/app-config

cd ${HOME}

# Update list of packages
sudo apt-get update

# Install Web Server deps
sudo apt-get install -y \
    apache2 \
    php5 \
    sqlite \
    php-pear \
    php5-sqlite  \
    sysv-rc-conf \
    php5-mcrypt \
    postfix \
    dovecot-common \
    dovecot-imapd \
    squirrelmail \
    ircd-hybrid

# Enabe php5-mcrypt
sudo php5enmod mcrypt

# Install cakephp with GitHub
#git clone -b 2.x git://github.com/cakephp/cakephp.git ~/projects/
#sudo mv -f ~/projects/lib/Cake /usr/share/php
#rm -rf ~/projects
# Install cakephp with pear
#sudo pear channel-discover pear.cakephp.org
#sudo pear install cakephp/CakePHP-2.8.3

# Checkout the HSMM-Pi project
if [ ! -e ${PROJECT_HOME} ]; then
    git clone https://github.com/cbegg50/app-config.git
else
    cd ${PROJECT_HOME}
    git pull
fi

# Set symlink to webapp
if [ -d /var/www/html ]; then
    cd /var/www/html
else
    cd /var/www
fi
if [ ! -d app-config ]; then
    sudo ln -s ${PROJECT_HOME}/src/var/www/app-config
fi
sudo rm -f index.html
sudo ln -s ${PROJECT_HOME}/src/var/www/index.html

cd ${PROJECT_HOME}
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '070854512ef404f16bac87071a6db9fd9721da1684cd4589b1196c3faf71b9a2682e2311b36a5079825e155ac7ce150d') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
php composer.phar install
sudo mv Vendor/cakephp/cakephp/lib/Cake /usr/share/php
rm -rf Vendor composer.phar composer.lock

cd ${PROJECT_HOME}/src/var/www/app-config

# Create temporary directory used by APP-CONFIG webapp, granting write priv's to www-data
mkdir -p tmp/cache/models
mkdir -p tmp/cache/persistent
mkdir -p tmp/logs
mkdir -p tmp/persistent
sudo chgrp -R www-data tmp
sudo chmod -R 775 tmp

# Extensively modified GM4WZG 10 Feb 2015 
# Revision 1.1
# Email setup script
#

EMAILPATH=/etc/postfix/main.cf
sudo touch "$EMAILPATH"
sudo chmod 644 "$EMAILPATH"
sudo touch /etc/postfix/helo_access
sudo chmod 644 /etc/postfix/helo_access
sudo maildirmake.dovecot /etc/skel/Maildir
sudo maildirmake.dovecot /etc/skel/Maildir/.Drafts
sudo maildirmake.dovecot /etc/skel/Maildir/.Sent
sudo maildirmake.dovecot /etc/skel/Maildir/.Spam
sudo maildirmake.dovecot /etc/skel/Maildir/.Trash
sudo maildirmake.dovecot /etc/skel/Maildir/.Templates

IRCDPATH=/etc/ircd-hybrid/ircd.conf
sudo touch "$IRCDPATH"
sudo chown irc "$IRCDPATH"
sudo chgrp irc "$IRCDPATH"

# Set permissions on system files to give www-data group write priv's
for file in /etc/postfix/main.cf /etc/postfix/helo_access /etc/ircd-hybrid/ircd.conf; do
    sudo chgrp www-data ${file}
    sudo chmod g+w ${file}
done

sudo mkdir -p /var/data/app-config
sudo chown root.www-data /var/data/app-config
sudo chmod 775 /var/data/app-config
if [ ! -e /var/data/app-config/app-config.sqlite ]; then
    sudo Console/cake schema create -y
    sudo chown root.www-data /var/data/app-config/app-config.sqlite
    sudo chmod 664 /var/data/app-config/app-config.sqlite
fi

# Check if our sudo commands have been enabled for www-data
OUTPUT=`sudo grep "www-data ALL=(ALL) NOPASSWD: /usr/sbin/adduser" /etc/sudoers`
if [ -z "$OUTPUT" ]; then
 sudo bash -c "echo 'www-data ALL=(ALL) NOPASSWD: /usr/sbin/adduser' >> /etc/sudoers"
 sudo bash -c "echo 'www-data ALL=(ALL) NOPASSWD: /usr/sbin/deluser' >> /etc/sudoers"
 sudo bash -c "echo 'www-data ALL=(ALL) NOPASSWD: /usr/bin/passwd' >> /etc/sudoers"
 sudo bash -c "echo 'www-data ALL=(ALL) NOPASSWD: /usr/sbin/service' >> /etc/sudoers"
fi
# enable apache mod-rewrite and ssl
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2ensite default-ssl

if [ -d /etc/apache2/conf.d ]; then
    sudo cp ${PROJECT_HOME}/src/etc/apache2/conf.d/app-config.conf /etc/apache2/conf.d/app-config.conf
    sudo cp ${PROJECT_HOME}/src/etc/apache2/conf.d/squirrelmail.conf /etc/apache2/conf.d/squirrelmail.conf
elif [ -d /etc/apache2/conf-available ]; then
    sudo cp ${PROJECT_HOME}/src/etc/apache2/conf-available/app-config.conf /etc/apache2/conf-available/app-config.conf
    sudo cp ${PROJECT_HOME}/src/etc/apache2/conf-available/squirrelmail.conf /etc/apache2/conf-available/squirrelmail.conf
    sudo a2enconf app-config
    sudo a2enconf squirrelmail
fi
sudo service apache2 restart

# install mail files
sudo cp  ${PROJECT_HOME}/src/etc/postfix/master.cf /etc/postfix
sudo cp -r  ${PROJECT_HOME}/src/etc/dovecot/* /etc/dovecot

# enable services
#sudo sysv-rc-conf --level 2345 olsrd on
#sudo sysv-rc-conf --level 2345 dnsmasq on
#sudo sysv-rc-conf --level 2345 gpsd on

# install CRON jobs
sudo cp ${PROJECT_HOME}/src/etc/cron.d/* /etc/cron.d/

# print success message if we make it this far
printf "\n\n---- SUCCESS ----\n\nLogin to the web console to configure the apps\n"
