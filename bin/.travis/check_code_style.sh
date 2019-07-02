#!/bin/bash

if [[ "${TRAVIS_PULL_REQUEST}" != "false" ]]
then
    # limit checking to *.php files which were changed by the current PR
    # HEAD^1 is a merge commit of all changes and it's strictly related to how Travis checks out PRs
    FILES_LIST=$(git diff HEAD^1 --diff-filter=ACMR --name-only "*.php"|paste -sd ' ');
    if [[ -z "${FILES_LIST}" ]]
    then
        echo "> Code Style check: nothing to check"
        exit 0;
    fi
    echo "> Code Style check: checking the following files: ${FILES_LIST}";
else
    # for non-PR builds check entire codebase
    FILES_LIST=""
    echo "> Code Style check: checking the entire codebase";
fi

php ./vendor/bin/php-cs-fixer fix \
    --config=.php_cs \
    --path-mode=intersection \
    --dry-run -v \
    --show-progress=estimating ${FILES_LIST};
