<?php
namespace Common\Lib;

interface ElasticsearchModelContract{

    function elasticsearchIndexList();

    function elasticsearchAddDataParams();

    function isElasticsearchIndex($ent);

    function createIndex();
}