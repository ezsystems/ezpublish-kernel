#!/bin/bash
if [ -z "${DATABASE}" ];
then
	echo "Missing DATABASE environment variable. Please specify a DSN for a persistent database to make this server work.";
	exit -1;
fi

/usr/bin/env php -S localhost:8042 index.php
