#!/bin/sh

# File for setting up system for unit/integration testing

# Disable xdebug to speed things up as we don't currently generate coverge on travis
# And make sure we use UTF-8 encoding
if [ "$TRAVIS_PHP_VERSION" != "hhvm" ] ; then
    phpenv config-rm xdebug.ini
    echo "default_charset = UTF-8" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi

# Enable redis
if [ "$CUSTOM_CACHE_POOL" = "singleredis" ] ; then
    echo extension = redis.so >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi

# Setup DB
if [ "$DB" = "mysql" ] ; then
    # https://github.com/travis-ci/travis-ci/issues/3049
    # make sure we don't run out of entropy apparently (see link above)
    sudo apt-get -y install haveged
    sudo service haveged start
    # make tmpfs and run MySQL on it for reasonable performance
    sudo mkdir /mnt/ramdisk
    sudo mount -t tmpfs -o size=1024m tmpfs /mnt/ramdisk
    sudo stop mysql
    sudo mv /var/lib/mysql /mnt/ramdisk
    sudo ln -s /mnt/ramdisk/mysql /var/lib/mysql
    sudo start mysql
    # Install test db
    mysql -e "CREATE DATABASE IF NOT EXISTS testdb DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;" -uroot
fi
if [ "$DB" = "postgresql" ] ; then psql -c "CREATE DATABASE testdb;" -U postgres ; psql -c "CREATE EXTENSION pgcrypto;" -U postgres testdb ; fi

# Setup GitHub key to avoid api rate limit (pure auth read only key, no rights, for use by ezsystems repos only!)
composer config -g github-oauth.github.com "d0285ed5c8644f30547572ead2ed897431c1fc09"

COMPOSER_UPDATE=""

# solr package search API integration tests
if [ "$TEST_CONFIG" = "phpunit-integration-legacy-solr.xml" ] ; then
    echo "> Require ezsystems/ezplatform-solr-search-engine:^1.0.0@dev"
    composer require --no-update ezsystems/ezplatform-solr-search-engine:^1.0.0@dev
    COMPOSER_UPDATE="true"
fi

# Switch to another Symfony version if asked for
if [ "$SYMFONY_VERSION" != "" ] ; then
    echo "> Update symfony/symfony requirement to ${SYMFONY_VERSION}"
    composer require --no-update symfony/symfony="${SYMFONY_VERSION}"
    COMPOSER_UPDATE="true"
fi

# Install packages with composer update if asked for to make sure not use composer.lock if present
if [ "$COMPOSER_UPDATE" = "true" ] ; then
    echo "> Install dependencies through Composer (using update as other packages was requested)"
    composer update --no-progress --no-interaction --prefer-dist
else
    echo "> Install dependencies through Composer"
    composer install --no-progress --no-interaction --prefer-dist
fi

# Setup Solr / Elastic search if asked for
if [ "$TEST_CONFIG" = "phpunit-integration-legacy-elasticsearch.xml" ] ; then ./bin/.travis/init_elasticsearch.sh ; fi
if [ "$TEST_CONFIG" = "phpunit-integration-legacy-solr.xml" ] ; then ./vendor/ezsystems/ezplatform-solr-search-engine/bin/.travis/init_solr.sh; fi
