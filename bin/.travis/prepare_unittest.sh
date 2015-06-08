#!/bin/sh

# File for setting up system for unit/integration testing

# Disable xdebug to speed things up as we don't currently generate coverge on travis
if [ "$TRAVIS_PHP_VERSION" != "hhvm" ] ; then phpenv config-rm xdebug.ini ; fi

# Setup DB
if [ "$DB" = "mysql" ] ; then mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" -uroot ; fi
if [ "$DB" = "postgresql" ] ; then psql -c "CREATE DATABASE $DB_NAME;" -U postgres ; psql -c "CREATE EXTENSION pgcrypto;" -U postgres $DB_NAME ; fi

# Setup github key to avoid api rate limit
./bin/.travis/install_composer_github_key.sh

# Update composer to newest version
composer self-update

# Switch to another Symfony version if asked for
if [ "$SYMFONY_VERSION" != "" ] ; then composer require --no-update symfony/symfony=$SYMFONY_VERSION ; fi;

# Install packages using composer
composer install --prefer-dist

# Setup Solr / Elastic search if asked for
if [ "$TEST_CONFIG" = "phpunit-integration-legacy-elasticsearch.xml" ] ; then ./bin/.travis/init_elasticsearch.sh ; fi
if [ "$TEST_CONFIG" = "phpunit-integration-legacy-solr-multicore.xml" ] ; then ./bin/.travis/init_solr.sh ; fi
if [ "$TEST_CONFIG" = "phpunit-integration-legacy-solr-multicore-mix.xml" ] ; then ./bin/.travis/init_solr.sh ; fi
if [ "$TEST_CONFIG" = "phpunit-integration-legacy-solr-single-core.xml" ] ; then curl -L https://raw.github.com/andrerom/travis-solr/410/travis-solr.sh | SOLR_CONFS=eZ/Publish/Core/Search/Solr/Content/Resources/schema.xml bash ; fi
