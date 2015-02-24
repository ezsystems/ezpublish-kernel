#!/bin/sh

# File for setting up system for behat testing, just like done in DemoBundle's .travis.yml

git fetch --unshallow && git checkout -b tmp_travis_branch
export BRANCH_BUILD_DIR=$TRAVIS_BUILD_DIR
export TRAVIS_BUILD_DIR="$HOME/build/ezpublish-community"
cd "$HOME/build"

# Change the branch and/or remote to use a different ezpublish branch/distro
git clone --depth 1 --single-branch --branch travis_improvements https://github.com/ezsystems/ezpublish-community.git
cd ezpublish-community


./bin/.travis/setup_from_external_repo.sh $BRANCH_BUILD_DIR "ezsystems/ezpublish-kernel:dev-tmp_travis_branch"
