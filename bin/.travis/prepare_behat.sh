#!/bin/sh

# File for setting up system for behat testing, just like done in DemoBundle's .travis.yml

export BRANCH_BUILD_DIR=$TRAVIS_BUILD_DIR
export TRAVIS_BUILD_DIR="$HOME/build/ezpublish-community"
cd "$HOME/build"

# Change the branch and/or remote to use a different ezpublish branch/distro
git clone --depth 1 --single-branch --branch master https://github.com/ezsystems/ezpublish-community.git
cd ezpublish-community

# Use this if you depend on another branch for a dependency (only works for the ezsystems remote)
# (note that packagist may take time to update the references, leading to errors. Just retrigger the build)
#
# Example:
# composer require --no-update ezsystems/DemoBundle:dev-MyCustomBranch

# Prepare system (Apache, Mysql, Sahi/Selenium, eZ Publish)
./bin/.travis/prepare_system.sh
./bin/.travis/prepare_testsystem.sh
./bin/.travis/prepare_ezpublish.sh

# Replace kernel with the one from pull-request/current checkout
rm -rf vendor/ezsystems/ezpublish-kernel
mv "$BRANCH_BUILD_DIR" vendor/ezsystems/ezpublish-kernel
