#!/bin/sh

# File for setting up system for unit/integration testing

# Disable xdebug to speed things up as we don't currently generate coverge on travis
if [ "$TRAVIS_PHP_VERSION" != "hhvm" ] ; then phpenv config-rm xdebug.ini ; fi

# Setup DB
if [ "$DB" = "mysql" ] ; then mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" -uroot ; fi
if [ "$DB" = "postgresql" ] ; then psql -c "CREATE DATABASE $DB_NAME;" -U postgres ; psql -c "CREATE EXTENSION pgcrypto;" -U postgres $DB_NAME ; fi

echo "> Setup github auth key to not reach api limit"
cp bin/.travis/composer-auth.json ~/.composer/auth.json

# Switch to another Symfony version if asked for (with composer update to not use composer.lock if present)
if [ "$SYMFONY_VERSION" != "" ] ; then
    echo "> Install dependencies through Composer (with custom Symfony version: ${SYMFONY_VERSION})"
    composer require --no-update symfony/symfony="${SYMFONY_VERSION}"
    composer update --no-progress --no-interaction --prefer-dist
else
    echo "> Install dependencies through Composer"
    composer install --no-progress --no-interaction --prefer-dist
fi

# Setup Solr / Elastic search if asked for
if [ "$TEST_CONFIG" = "phpunit-integration-legacy-elasticsearch.xml" ] ; then ./bin/.travis/init_elasticsearch.sh ; fi
if [ "$TEST_CONFIG" = "phpunit-integration-legacy-solr.xml" ] ; then
    composer require -v --no-progress --no-interaction ezsystems/ezplatform-solr-search-engine:dev-master
    ./vendor/ezsystems/ezplatform-solr-search-engine/bin/.travis/init_solr.sh
fi
