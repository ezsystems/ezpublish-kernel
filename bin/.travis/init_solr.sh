#!/usr/bin/env bash

SOLR_PORT=${SOLR_PORT:-8983}
SOLR_VERSION=${SOLR_VERSION:-4.10.4}
DEBUG=${DEBUG:-false}

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
    mode=$3
    # Run solr
    echo "Running with folder ${dir_name} in ${mode} mode"
    echo "Starting solr on port ${solr_port}..."

    # go to the solr folder
    cd $dir_name/example

    if [ "$DEBUG" = "true" ]
    then
        if [ "$mode" = "multi" ]
        then
            java -Djetty.port=$solr_port -Dsolr.solr.home=multicore -jar start.jar &
        else
            java -Djetty.port=$solr_port -jar start.jar &
        fi
    else
        if [ "$mode" = "multi" ]
        then
            java -Djetty.port=$solr_port -Dsolr.solr.home=multicore -jar start.jar > /dev/null 2>&1 &
        else
            java -Djetty.port=$solr_port -jar start.jar > /dev/null 2>&1 &
        fi
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
        4.10.4)
            url="http://archive.apache.org/dist/lucene/solr/4.10.4/solr-4.10.4.tgz"
            dir_name="solr-4.10.4"
            dir_conf="collection1/conf/"
            ;;
    esac

    download $url $dir_name

    if [ ${#SOLR_CORES[@]} -eq 0 ]; then
        destination_dir_name="$dir_name/example/solr/$dir_conf"
        copy_configuration $destination_dir_name
        mode="single"
    else
        # remove default cores configuration
        sed -i.bak 's/<core name=".*" instanceDir=".*" \/>//g' $dir_name/example/multicore/solr.xml
        for solr_core in ${SOLR_CORES[@]};
        do
            add_core $dir_name $dir_conf $solr_core
        done
        mode="multi"
    fi

    run $dir_name $SOLR_PORT $mode
}

add_core() {
    dir_name=$1
    dir_conf=$2
    solr_core=$3

    # add core configuration
    sed -i.bak "s/<shardHandlerFactory/<core name=\"$solr_core\" instanceDir=\"$solr_core\" \/><shardHandlerFactory/g" $dir_name/example/multicore/solr.xml

    # prepare core directories
    [[ -d "${dir_name}/example/multicore/${solr_core}" ]] || mkdir $dir_name/example/multicore/$solr_core
    [[ -d "${dir_name}/example/multicore/${solr_core}/conf" ]] || mkdir $dir_name/example/multicore/$solr_core/conf

    # copy currency.xml, stopwords.txt and synonyms.txt
    cp $dir_name/example/solr/collection1/conf/currency.xml $dir_name/example/multicore/$solr_core/conf/
    cp $dir_name/example/solr/collection1/conf/stopwords.txt $dir_name/example/multicore/$solr_core/conf/
    cp $dir_name/example/solr/collection1/conf/synonyms.txt $dir_name/example/multicore/$solr_core/conf/

    # copy core0 solrconfig.xml and patch it for current core
    if [ ! -f $dir_name/example/multicore/$solr_core/conf/solrconfig.xml ]; then
        cp $dir_name/example/multicore/core0/conf/solrconfig.xml $dir_name/example/multicore/$solr_core/conf/
        sed -i.bak s/core0/"$solr_core"/g $dir_name/example/multicore/$solr_core/conf/solrconfig.xml
    fi

    destination_dir_name="$dir_name/example/multicore/$solr_core/conf"
    copy_configuration $destination_dir_name
}

copy_configuration() {
    destination_dir_name=$1

    if [ -d "${SOLR_CONFS}" ] ; then
      cp -R $SOLR_CONFS/* $destination_dir_name
    else
      for file in $SOLR_CONFS
      do
        if [ -f "${file}" ]; then
            cp $file $destination_dir_name
            echo "Copied $file into solr conf directory."
        else
            echo "${file} is not valid";
            exit 1
        fi
      done
    fi
}

check_version() {
    case $1 in
        3.5.0|3.6.0|3.6.1|3.6.2|4.0.0|4.1.0|4.2.0|4.2.1|4.3.1|4.4.0|4.5.0|4.5.1|4.6.0|4.6.1|4.7.0|4.7.1|4.7.2|4.8.0|4.8.1|4.9.0|4.9.1|4.10.0|4.10.1|4.10.2|4.10.3|4.10.4);;
        *)
            echo "Sorry, $1 is not supported or not valid version."
            exit 1
            ;;
    esac
}

check_version $SOLR_VERSION
download_and_run $SOLR_VERSION
