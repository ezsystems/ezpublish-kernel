#!/usr/bin/env sh

cd $HOME/build/ezplatform/
sed -i "s@#        ezpublish_rest:@        ezpublish_rest:@" app/config/security.yml
sed -i "s@#            pattern: ^/api/ezp/v2@            pattern: ^/api/ezp/v2@" app/config/security.yml
sed -i "s@#            stateless: true@            stateless: true@" app/config/security.yml
sed -i "s@#            ezpublish_http_basic:@            ezpublish_http_basic:@" app/config/security.yml
sed -i "s@#                realm: eZ Publish REST API@                realm: eZ Publish REST API@" app/config/security.yml
php app/console cache:clear --env=behat
