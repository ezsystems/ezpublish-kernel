#!/bin/sh

if [ "$#" -ne 2 ]; then
    echo "Usage: sch2xsl.sh source_file target_file"
    echo "Illegal number of parameters, exiting"
    exit 1
fi

# get script directory
SCRIPT=$(readlink -f "$0") # take into account symlinks
SCRIPTPATH=$(dirname "$SCRIPT")

xsltproc "${SCRIPTPATH}/iso_dsdl_include.xsl" $1 | xsltproc "${SCRIPTPATH}/iso_abstract_expand.xsl" - | xsltproc --output $2 "${SCRIPTPATH}/iso_svrl_for_xslt1.xsl" -
