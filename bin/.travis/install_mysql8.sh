#!/usr/bin/env bash

set -ex

echo "Starting MySQL 8.0..."

echo -e "[mysqld]\ndefault_authentication_plugin=mysql_native_password" > /tmp/mysql-auth.cnf

docker pull mysql:8.0
docker run \
    -d \
    -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
    -e MYSQL_DATABASE=testdb \
    -v /tmp/mysql-auth.cnf:/etc/mysql/conf.d/auth.cnf:ro \
    -p 33306:3306 \
    --name mysql80 \
    mysql:8.0
