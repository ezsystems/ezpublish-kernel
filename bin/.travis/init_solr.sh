#!/usr/bin/env bash

SOLR_PORT=${SOLR_PORT:-8983}
SOLR_VERSION=${SOLR_VERSION:-4.9.1}
DEBUG=${DEBUG:-false}
SOLR_CORE=${SOLR_CORE:-core0}

download() {
    FILE="$2.tgz"
    if [ -f $FILE ];
    then
       echo "File $FILE exists."
       tar -zxf $FILE
    else
       echo "File $FILE does not exist. Downloading solr from $1..."
       curl -O $1
       tar -zxf $FILE
    fi
    echo "Downloaded!"
}

is_solr_up(){
    echo "Checking if solr is up on http://localhost:$SOLR_PORT/solr/admin/cores"
    http_code=`echo $(curl -s -o /dev/null -w "%{http_code}" "http://localhost:$SOLR_PORT/solr/admin/cores")`
    return `test $http_code = "200"`
}

wait_for_solr(){
    while ! is_solr_up; do
        sleep 3
    done
}

run() {
    dir_name=$1
    solr_port=$2
    solr_core=$3
    # Run solr
    echo "Running with folder $dir_name"
    echo "Starting solr on port ${solr_port}..."

    # go to the solr folder
    cd $1/example

    if [ "$DEBUG" = "true" ]
    then
        java -Djetty.port=$solr_port -Dsolr.solr.home=multicore -jar start.jar &
    else
        java -Djetty.port=$solr_port -Dsolr.solr.home=multicore -jar start.jar > /dev/null 2>&1 &
    fi
    wait_for_solr
    cd ../../
    echo "Started"
}


download_and_run() {
    case $1 in
        3.5.0)
            url="http://archive.apache.org/dist/lucene/solr/3.5.0/apache-solr-3.5.0.tgz"
            dir_name="apache-solr-3.5.0"
            dir_conf="conf/"
            ;;
        3.6.0)
            url="http://archive.apache.org/dist/lucene/solr/3.6.0/apache-solr-3.6.0.tgz"
            dir_name="apache-solr-3.6.0"
            dir_conf="conf/"
            ;;
        3.6.1)
            url="http://archive.apache.org/dist/lucene/solr/3.6.1/apache-solr-3.6.1.tgz"
            dir_name="apache-solr-3.6.1"
            dir_conf="conf/"
            ;;
        3.6.2)
            url="http://archive.apache.org/dist/lucene/solr/3.6.2/apache-solr-3.6.2.tgz"
            dir_name="apache-solr-3.6.2"
            dir_conf="conf/"
            ;;
        4.0.0)
            url="http://archive.apache.org/dist/lucene/solr/4.0.0/apache-solr-4.0.0.tgz"
            dir_name="apache-solr-4.0.0"
            dir_conf="collection1/conf/"
            ;;
        4.1.0)
            url="http://archive.apache.org/dist/lucene/solr/4.1.0/solr-4.1.0.tgz"
            dir_name="solr-4.1.0"
            dir_conf="collection1/conf/"
            ;;
        4.2.0)
            url="http://archive.apache.org/dist/lucene/solr/4.2.0/solr-4.2.0.tgz"
            dir_name="solr-4.2.0"
            dir_conf="collection1/conf/"
            ;;
        4.2.1)
            url="http://archive.apache.org/dist/lucene/solr/4.2.1/solr-4.2.1.tgz"
            dir_name="solr-4.2.1"
            dir_conf="collection1/conf/"
            ;;
        4.3.1)
            url="http://archive.apache.org/dist/lucene/solr/4.3.1/solr-4.3.1.tgz"
            dir_name="solr-4.3.1"
            dir_conf="collection1/conf/"
            ;;
        4.4.0)
            url="http://archive.apache.org/dist/lucene/solr/4.4.0/solr-4.4.0.tgz"
            dir_name="solr-4.4.0"
            dir_conf="collection1/conf/"
            ;;
        4.5.0)
            url="http://archive.apache.org/dist/lucene/solr/4.5.0/solr-4.5.0.tgz"
            dir_name="solr-4.5.0"
            dir_conf="collection1/conf/"
            ;;
        4.5.1)
            url="http://archive.apache.org/dist/lucene/solr/4.5.1/solr-4.5.1.tgz"
            dir_name="solr-4.5.1"
            dir_conf="collection1/conf/"
            ;;
        4.6.0)
            url="http://archive.apache.org/dist/lucene/solr/4.6.0/solr-4.6.0.tgz"
            dir_name="solr-4.6.0"
            dir_conf="collection1/conf/"
            ;;
        4.6.1)
            url="http://archive.apache.org/dist/lucene/solr/4.6.1/solr-4.6.1.tgz"
            dir_name="solr-4.6.1"
            dir_conf="collection1/conf/"
            ;;
        4.7.0)
            url="http://archive.apache.org/dist/lucene/solr/4.7.0/solr-4.7.0.tgz"
            dir_name="solr-4.7.0"
            dir_conf="collection1/conf/"
            ;;
        4.7.1)
            url="http://archive.apache.org/dist/lucene/solr/4.7.1/solr-4.7.1.tgz"
            dir_name="solr-4.7.1"
            dir_conf="collection1/conf/"
            ;;
        4.7.2)
            url="http://archive.apache.org/dist/lucene/solr/4.7.2/solr-4.7.2.tgz"
            dir_name="solr-4.7.2"
            dir_conf="collection1/conf/"
            ;;
        4.8.0)
            url="http://archive.apache.org/dist/lucene/solr/4.8.0/solr-4.8.0.tgz"
            dir_name="solr-4.8.0"
            dir_conf="collection1/conf/"
            ;;
        4.8.1)
            url="http://archive.apache.org/dist/lucene/solr/4.8.1/solr-4.8.1.tgz"
            dir_name="solr-4.8.1"
            dir_conf="collection1/conf/"
            ;;
        4.9.0)
            url="http://archive.apache.org/dist/lucene/solr/4.9.0/solr-4.9.0.tgz"
            dir_name="solr-4.9.0"
            dir_conf="collection1/conf/"
            ;;
        4.9.1)
            url="http://archive.apache.org/dist/lucene/solr/4.9.1/solr-4.9.1.tgz"
            dir_name="solr-4.9.1"
            dir_conf="collection1/conf/"
            ;;
        4.10.0)
            url="http://archive.apache.org/dist/lucene/solr/4.10.0/solr-4.10.0.tgz"
            dir_name="solr-4.10.0"
            dir_conf="collection1/conf/"
            ;;
        4.10.1)
            url="http://archive.apache.org/dist/lucene/solr/4.10.1/solr-4.10.1.tgz"
            dir_name="solr-4.10.1"
            dir_conf="collection1/conf/"
            ;;
        4.10.2)
            url="http://archive.apache.org/dist/lucene/solr/4.10.2/solr-4.10.2.tgz"
            dir_name="solr-4.10.2"
            dir_conf="collection1/conf/"
            ;;
        4.10.3)
            url="http://archive.apache.org/dist/lucene/solr/4.10.3/solr-4.10.3.tgz"
            dir_name="solr-4.10.3"
            dir_conf="collection1/conf/"
            ;;
    esac

    download $url $dir_name
    add_core $dir_name $dir_conf core0 $SOLR_CONFS
    add_core $dir_name $dir_conf core1 $SOLR_CONFS
    add_core $dir_name $dir_conf core2 $SOLR_CONFS
    add_core $dir_name $dir_conf core3 $SOLR_CONFS
    run $dir_name $SOLR_PORT $SOLR_CORE

    if [ -z "${SOLR_DOCS}" ]
    then
        echo "$solr_docs not defined, skipping initial indexing"
    else
        post_documents $dir_name $SOLR_DOCS $SOLR_CORE $SOLR_PORT
    fi
}

add_core() {
    dir_name=$1
    dir_conf=$2
    solr_core=$3
    solr_confs=$4
    # prepare our folders
    [[ -d "${dir_name}/example/multicore/${solr_core}" ]] || mkdir $dir_name/example/multicore/$solr_core
    [[ -d "${dir_name}/example/multicore/${solr_core}/conf" ]] || mkdir $dir_name/example/multicore/$solr_core/conf

    cp $dir_name/example/solr/collection1/conf/currency.xml $dir_name/example/multicore/$solr_core/conf/
    cp $dir_name/example/solr/collection1/conf/stopwords.txt $dir_name/example/multicore/$solr_core/conf/
    cp $dir_name/example/solr/collection1/conf/synonyms.txt $dir_name/example/multicore/$solr_core/conf/

    # copies custom configurations
    if [ -d "${solr_confs}" ] ; then
      cp -R $solr_confs/* $dir_name/example/multicore/$solr_core/conf/
    else
      for file in $solr_confs
      do
        if [ -f "${file}" ]; then
            cp $file $dir_name/example/multicore/$solr_core/conf
            echo "Copied $file into solr conf directory."
        else
            echo "${file} is not valid";
            exit 1
        fi
      done
    fi
}

post_documents() {
    dir_name=$1
    solr_docs=$2
    solr_core=$3
    solr_port=$4
      # Post documents
    if [ -z "${solr_docs}" ]
    then
        echo "SOLR_DOCS not defined, skipping initial indexing"
    else
        echo "Indexing $solr_docs"
        java -Dtype=application/json -Durl=http://localhost:$solr_port/solr/$solr_core/update/json -jar $dir_name/example/exampledocs/post.jar $solr_docs
    fi
}

check_version() {
    case $1 in
        3.5.0|3.6.0|3.6.1|3.6.2|4.0.0|4.1.0|4.2.0|4.2.1|4.3.1|4.4.0|4.5.0|4.5.1|4.6.0|4.6.1|4.7.0|4.7.1|4.7.2|4.8.0|4.8.1|4.9.0|4.9.1|4.10.0|4.10.1|4.10.2|4.10.3);;
        *)
            echo "Sorry, $1 is not supported or not valid version."
            exit 1
            ;;
    esac
}

check_version $SOLR_VERSION
download_and_run $SOLR_VERSION
