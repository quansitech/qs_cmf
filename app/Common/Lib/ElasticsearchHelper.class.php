<?php
namespace Common\Lib;

use Think\Exception;

trait ElasticsearchHelper{

    protected $_deleted_ents;

    public function addAll(){
        E('Elasticsearch model  batch action is forbidden');
    }

    public function selectAdd($fields='',$table='',$options=array()){
        E('Elasticsearch model  batch action is forbidden');
    }

    protected function compileParams($ent, $params = []){
        if(!$this instanceof ElasticsearchModelContract){
            E('model must implements ElasticsearchModelContract');
        }

        if(empty($params)){
            $params = self::elasticsearchAddDataParams();
        }

        if(!is_array($params)){
            E('elasticsearch params must be array type');
        }

        foreach($params as $k => $v){
            if(is_array($v)){
                $params[$k] = self::compileParams($ent, $v);
            }

            if(preg_match('#:([A-Za-z_]+)(?:{([A-Za-z_]+)})?#', $v, $match)){

                $value = $ent[$match[1]];
                if(isset($match[2])){
                    $value = $value . $match[2];
                }
                $params[$k] = $value;
            }
        }

        return $params;
    }

    protected function _after_insert($data,$options) {
        if(parent::_after_insert($data, $options) === false){
            return false;
        }

        if(self::isElasticsearchIndex($data) === false){
            return true;
        }

        $params = self::compileParams($data);

        Elasticsearch::getBuilder()->index($params);
    }

    protected function _after_update($data,$options) {
        if(parent::_after_update($data, $options) === false){
            return false;
        }

        $elastic_builder = Elasticsearch::getBuilder();

        $ents = $this->where($options['where'])->select();
        if(!$ents){
            return true;
        }
        foreach($ents as $ent){
            $params = self::compileParams($ent);

            if(self::isElasticsearchIndex($ent) === false){
                self::deleteIndex($params);
            }
            else{
                $elastic_builder->index($params);
            }
        }
    }

    protected function _before_delete($options){
        if(parent::_before_delete($options) === false){
            return false;
        }

        $this->_deleted_ents = $this->where($options['where'])->select();
    }

    protected function _after_delete($data,$options) {
        if(parent::_after_delete($data, $options) === false){
            return false;
        }

        foreach($this->_deleted_ents as $ent){
            $params = self::compileParams($ent);

            self::deleteIndex($params);
        }
    }

    protected function deleteIndex($params){
        $params = [
            'index' => $params['index'],
            'type' => $params['type'],
            'id' => $params['id'],
        ];

        $elastic_builder = Elasticsearch::getBuilder();

        try{
            $result = $elastic_builder->get($params);

            if($result['found'] === true){
                $elastic_builder->delete($params);
            }
        }
        catch(\Exception $e){

        }
    }

    public function createIndex(){
        $ents = self::elasticsearchIndexList();
        $n = 0;
        foreach($ents as $ent){
            $params = self::compileParams($ent);

            Elasticsearch::getBuilder()->index($params);
            $n++;
        }
        return $n;
    }
}