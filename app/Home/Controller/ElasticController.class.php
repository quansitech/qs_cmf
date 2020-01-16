<?php
namespace Home\Controller;


use Illuminate\Support\Str;
use Qscmf\Lib\Elasticsearch\Elasticsearch;
use Exception;
use Qscmf\Lib\Elasticsearch\ElasticsearchModelContract;

if (!IS_CLI)  die('The file can only be run in cli mode!');

class ElasticController{

    public function index(){
        S('Elasticsearch_index_creating', 'prepare');
        $result = M()->query('show tables');

        $client = Elasticsearch::getBuilder();
        $params = ['index' => 'global_search'];
        try{
            $client->indices()->delete($params);
        }
        catch (Exception $e){

        }

        $params = [
            'index' => 'global_search',
            'body' => [
                'mappings' => [
                    'content' => [
                        'properties' => [
                            'title' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                                "search_analyzer" => "ik_max_word"
                            ],
                            'desc' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                                "search_analyzer"=> "ik_max_word"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $client->indices()->create($params);

        $sum = 0;
        foreach($result as $v){
            $table = array_pop($v);
            if(strpos($table, C('DB_PREFIX')) === false){
                continue;
            }
            $model_name = parse_name(Str::replaceFirst(C('DB_PREFIX'), '', $table), 1);
            if(D($model_name) instanceof ElasticsearchModelContract){
                $sum += D($model_name)->createIndex();
                S('Elasticsearch_index_creating', $sum);
            }
        }
        S('Elasticsearch_index_creating', 'finished');
    }
}