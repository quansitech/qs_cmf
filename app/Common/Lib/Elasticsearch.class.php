<?php
namespace Common\Lib;

use Elasticsearch\ClientBuilder;

class Elasticsearch{

    static public function getBuilder(){
        return ClientBuilder::create()->setHosts(C('ELASTICSEARCH_HOSTS'))->build();
    }
}