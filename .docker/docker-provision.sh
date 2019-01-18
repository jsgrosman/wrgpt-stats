#!/bin/sh

apt-get -q update
apt-get upgrade -y

echo "-- Installing node and dependencies"
apt-get install --yes gnupg
curl -sL https://deb.nodesource.com/setup_10.x | bash -
apt-get -q install --yes nodejs

echo "-- Installing git"
apt-get -q install --yes git

echo "-- Installing support for DateFormat in PHP"
apt-get install -y zlib1g-dev libicu-dev g++
docker-php-ext-configure intl
docker-php-ext-install intl

echo "-- Install support for PDO in PHP"
docker-php-ext-install pdo pdo_mysql mysqli

echo "-- Turning on rewrite rules in Apache"
a2enmod rewrite

echo "running php composer"
php composer.phar self-update
php composer.phar install

echo "running npm"
cd /app/www
npm install
