#!/usr/bin/env sh

cd $HOME/build/ezplatform/
sed -i "s@#        ezpublish_rest:@        ezpublish_rest:@" ezpublish/config/security.yml
sed -i "s@#            pattern: ^/api/ezp/v2@            pattern: ^/api/ezp/v2@" ezpublish/config/security.yml
sed -i "s@#            stateless: true@            stateless: true@" ezpublish/config/security.yml
sed -i "s@#            ezpublish_http_basic:@            ezpublish_http_basic:@" ezpublish/config/security.yml
sed -i "s@#                realm: eZ Publish REST API@                realm: eZ Publish REST API@" ezpublish/config/security.yml
php ezpublish/console cache:clear --env=behat
