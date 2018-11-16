#!/usr/bin/env bash

set -ex

sudo mkdir /mnt/ramdisk
sudo mount -t tmpfs -o size=1024m tmpfs /mnt/ramdisk
sudo stop mysql

echo -e "[mysqld]\ndefault_authentication_plugin=mysql_native_password\nskip-log-bin\nssl=0" > /tmp/mysql-auth.cnf

docker pull mysql:8.0.11
docker run -d -e MYSQL_ALLOW_EMPTY_PASSWORD=yes -v /tmp/mysql-auth.cnf:/etc/mysql/conf.d/auth.cnf:ro -v /mnt/ramdisk:/var/lib/mysql -p 33306:3306 --name mysql80 mysql:8.0.11
docker exec mysql80 apt-get update
docker exec mysql80 apt-get -y install haveged
docker exec mysql80 service haveged start
docker exec mysql80 mysql -u root -e "CREATE DATABASE IF NOT EXISTS testdb DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;";
