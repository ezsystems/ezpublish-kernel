#!/usr/bin/env bash

set -ex

sudo mkdir /mnt/ramdisk
sudo mount -t tmpfs -o size=1024m tmpfs /mnt/ramdisk
sudo stop mysql
sudo start mysql

echo -e "[mysqld]\ndefault_authentication_plugin=mysql_native_password" > /tmp/mysql-auth.cnf

docker pull mysql:8.0
docker run -d -e MYSQL_ALLOW_EMPTY_PASSWORD=yes -e MYSQL_DATABASE=testdb -v /tmp/mysql-auth.cnf:/etc/mysql/conf.d/auth.cnf:ro -v /mnt/ramdisk:/var/lib/mysql -p 33306:3306 --name mysql80 mysql:8.0
docker exec mysql80 apt-get update
docker exec mysql80 apt-get -y install haveged
docker exec mysql80 service haveged start
