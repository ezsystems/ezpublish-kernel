#!/bin/sh

download() {
    echo "Downloading Elasticsearch from $1..."
    curl -s $1 | tar xz
    echo "Downloaded"
}

is_elasticsearch_up(){
    http_code=`echo $(curl -s -o /dev/null -w "%{http_code}" "http://localhost:9200")`
    return `test $http_code = "200"`
}

wait_for_elasticsearch(){
    while ! is_elasticsearch_up; do
        sleep 3
    done
}

run() {
    echo "Starting Elasticsearch..."
    cd $1/bin
    if [ $DEBUG ]
    then
        ./elasticsearch
    else
        ./elasticsearch > /dev/null 2>&1 &
    fi
    wait_for_elasticsearch
    sleep 60
    cd ../../
    echo "Started."
}

download_and_run() {
    url="http://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-$1.tar.gz"
    dir_name="elasticsearch-$1"
    resources_dir="eZ/Publish/Core/Search/Elasticsearch/Content/Resources"

    download $url

    # Configure mappings and index
    mkdir $dir_name/config/mappings
    mkdir $dir_name/config/mappings/ezpublish
    cp $resources_dir/mappings/content.json $dir_name/config/mappings/ezpublish
    cp $resources_dir/mappings/location.json $dir_name/config/mappings/ezpublish
    rm $dir_name/config/elasticsearch.yml
    cp $resources_dir/elasticsearch.yml $dir_name/config

    # Run elasticsearch
    run $dir_name
}

check_version() {
    case $1 in
        1.2.2|1.2.3|1.3.0|1.3.1|1.3.2|1.3.3|1.3.4|1.3.5|1.3.6|1.4.0|1.4.1|1.4.2);;
        *)
            echo "Sorry, $1 is not supported or not valid version."
            exit 1
            ;;
    esac
}

check_version $ELASTICSEARCH_VERSION
download_and_run $ELASTICSEARCH_VERSION
