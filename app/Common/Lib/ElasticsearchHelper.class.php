<?php
namespace Common\Lib;

use Think\Exception;

trait ElasticsearchHelper{

    protected $_deleted_ents;

    protected $_will_updated_ents;

    protected $index_columns;

    public function addAll(){
        E('Elasticsearch model  batch action is forbidden');
    }

    public function selectAdd($fields='',$table='',$options=array()){
        E('Elasticsearch model  batch action is forbidden');
    }

    protected function varAndConst($search, &$match){
        return preg_match('#^:([A-Za-z_]+)(?:{([A-Za-z0-9_]+)})?$#', $search, $match);
    }

    protected function varAndFunction($search, &$match){
        return preg_match('#^:([A-Za-z_]+)\|([A-Za-z0-9_]+)$#', $search, $match);
    }

    protected function getIndexColumn(){
        if($this->index_columns){
            return $this->index_columns;
        }

        if(!$this instanceof ElasticsearchModelContract){
            E('model must implements ElasticsearchModelContract');
        }

        $params = self::elasticsearchAddDataParams();

        $columns = [];
        foreach($params['body'] as $k => $v){
            if(self::varAndConst($v, $match)){
                array_push($columns, $match[1]);
            }
            else if(self::varAndFunction($v, $match)){
                array_push($columns, $match[1]);
            }
        }
        $this->index_columns = $columns;
        return $columns;
    }

    protected function isUpdateNeedForIndex($updated_ent){
        $index_columns = self::getIndexColumn();
        array_push($index_columns, $this->getPk());

        $updated_ent = array_intersect_key($updated_ent, array_flip($index_columns));

        return array_diff($this->_will_updated_ents[$updated_ent[$this->getPk()]],$updated_ent) ? true : false;
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

            if(self::varAndConst($v, $match)){
                $value = $ent[$match[1]];
                if(isset($match[2])){
                    $value = $value . $match[2];
                }
                $params[$k] = $value;
            }
            else if(self::varAndfunction($v, $match)){
                if(function_exists($match[2])){
                    $params[$k] = call_user_func($match[2], $ent[$match[1]]);
                }
                else{
                    $params[$k] = call_user_func([$this, $match[2]], $ent[$match[1]]);
                }
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

        self::genElasticIndex($params);
    }

    protected function _before_update($data,$options){
        if(parent::_before_update($data, $options) === false){
            return false;
        }

        $index_columns = [$this->getPk()];

        $index_columns = array_merge($index_columns, self::getIndexColumn());
        $this->_will_updated_ents = $this->where($options['where'])->getField($this->getPk() . ',' . implode(',', $index_columns));
    }

    protected function _after_update($data,$options) {
        if(parent::_after_update($data, $options) === false){
            return false;
        }

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
                self::isUpdateNeedForIndex($ent) && self::genElasticIndex($params);
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

        try{
            $elastic_builder = Elasticsearch::getBuilder();
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
            self::genElasticIndex($params);

            $n++;
        }
        return $n;
    }

    protected function genElasticIndex($params){
        try{
            Elasticsearch::getBuilder()->index($params);
        }
        catch(\Exception $e){
            if(C('ELASTIC_ALLOW_EXCEPTION')){
                E($e->getMessage());
            }
        }

    }
}