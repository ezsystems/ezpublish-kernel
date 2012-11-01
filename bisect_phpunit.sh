#!/bin/bash

# Execute phpunit with arguments (use case: directory filter), but stop on first failure / error
phpunit --colors --stop-on-failure --stop-on-error  $@

EXIT_CODE="$?"

if [ $EXIT_CODE -eq "255" ]; then
  # Wrapping the error code to 1, so bisect marks this build as "bad" and continues.
  exit 1
fi

if [ $EXIT_CODE -eq "2" ]; then
  # The testsuite does not exist, so we skip bisect here.
  exit 125
fi

exit $EXIT_CODE
