#!/bin/sh

# File for setting up system for unit/integration testing

# Enable redis
if [ "$CUSTOM_CACHE_POOL" = "singleredis" ] ; then
    echo 'extension = redis.so' >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

    # Configure redis to work in memory mode and avoid running out of memory
    redis-cli config set appendfsync "no"
    redis-cli config set maxmemory "60mb"
    # commented out to detect if a test uses more then max memory or if clearing is not done correctly between tests
    #redis-cli config set maxmemory-policy "allkeys-lru"
    redis-cli config set save ""
fi

# Setup DB
if [ "$DB" = "mysql" ] || [ "$DB" = "mariadb" ] ; then
    # https://github.com/travis-ci/travis-ci/issues/3049
    # make sure we don't run out of entropy apparently (see link above)
    sudo apt-get -y install haveged
    sudo service haveged start
    # make tmpfs and run MySQL on it for reasonable performance
    sudo mkdir /mnt/ramdisk
    sudo mount -t tmpfs -o size=1024m tmpfs /mnt/ramdisk
    sudo /etc/init.d/mysql stop
    sudo mv /var/lib/mysql /mnt/ramdisk
    sudo ln -s /mnt/ramdisk/mysql /var/lib/mysql
    sudo /etc/init.d/mysql start
    # Install test db
    mysql -e "CREATE DATABASE IF NOT EXISTS testdb DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;" -uroot
fi

# fix memory issue
if [ "$DB" = "postgresql" ] ; then sudo mount -o remount,size=25% /var/ramfs; psql -c "CREATE DATABASE testdb;" -U postgres ; psql -c "CREATE EXTENSION pgcrypto;" -U postgres testdb ; fi

# Setup GitHub key to avoid api rate limit (pure auth read only key, no rights, for use by ezsystems repos only!)
composer config -g github-oauth.github.com "d0285ed5c8644f30547572ead2ed897431c1fc09"

# solr package search API integration tests
if [ "$TEST_CONFIG" = "phpunit-integration-legacy-solr.xml" ] ; then
    # Install openJDK8 as default v11 is too new for SOLR
    sudo apt-get install openjdk-8-jdk
    echo "> Require ezsystems/ezplatform-solr-search-engine:^1.3.0@dev"
    composer require --no-update ezsystems/ezplatform-solr-search-engine:^1.3.0@dev
fi

# Switch to another Symfony version if asked for
if [ "$SYMFONY_VERSION" != "" ] ; then
    echo "> Update symfony/symfony requirement to ${SYMFONY_VERSION}"
    composer require --no-update symfony/symfony="${SYMFONY_VERSION}"
    # Remove php-cs-fixer as it is not needed for these tests and tends to cause Symfony version conflicts
    echo "> Remove php-cs-fixer"
    composer remove --dev --no-update friendsofphp/php-cs-fixer
fi
