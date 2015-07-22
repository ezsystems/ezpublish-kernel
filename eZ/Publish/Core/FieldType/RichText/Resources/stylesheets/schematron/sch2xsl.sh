#!/bin/sh

if [ "$#" -ne 2 ]; then
    echo "Usage: sch2xsl.sh source_file target_file"
    echo "Illegal number of parameters, exiting"
    exit 1
fi

xsltproc iso_dsdl_include.xsl $1 | xsltproc iso_abstract_expand.xsl - | xsltproc --output $2 iso_svrl_for_xslt1.xsl -
