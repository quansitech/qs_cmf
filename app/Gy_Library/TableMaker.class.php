<?php

namespace Gy_Library;

class TableMaker{
    
    protected $_columns = array();
    protected $_engine = 'MyISAM';
    protected $_charset = 'utf8mb4';
    protected $_table_name = '';
    protected $_error = '';


    //插入一个字段，格式 array('name' => 'cate_name', 'column_type' => 'varchar(32) not null', 'comment' => '类型名称')
    public function addColumns($column){
        $this->_columns[] = $column;
        return $this;
    }
    
    //设置数据表引擎 MyISAM or INNODB
    public function setEngine($engine){
        $this->_engine = $engine;
        return $this;
    }
    
    public function setCharset($charset){
        $this->_charset = $charset;
        return $this;
    }
    
    //设置表名，无需加上表前缀
    public function setTableName($table_name){
        $this->_table_name = C('DB_PREFIX') . $table_name;
        return $this;
    }
    
    public function getError(){
        return $this->_error;
    }
    
    protected function _isNotExistsTable(){
        if(empty($this->_table_name)){
            $this->_error = '未设置表名';
            return false;
        }
        
        $db_name = C('DB_NAME');
        $query_sql = "show tables where Tables_in_{$db_name}='{$this->_table_name}'";
        try{
            $row = M()->query($query_sql);
        }
        catch (\PDOException $ex){
            $this->_error = $ex->getMessage();
            return false;
        }
        
        if(!$row){
            return true;
        }
        else{
            $this->_error = '已有相同名字的表存在！';
            return false;
        }
    }
    
    public function make(){
        if($this->_isNotExistsTable() === false){
            return false;
        }
        
        //id 字段为默认主键
        $create_table_sql = "create table {$this->_table_name}(id int(11) not null AUTO_INCREMENT PRIMARY KEY,";
        
        foreach($this->_columns as $column){
            $create_table_sql .= "{$column['name']} {$column['column_type']}";
            if(isset($column['comment'])){
                $create_table_sql .= " comment '{$column['comment']}'";
            }
            $create_table_sql .= ',';
        }
        $create_table_sql = substr($create_table_sql, 0, strlen($create_table_sql) - 1);
        $create_table_sql .= ") ENGINE={$this->_engine} DEFAULT CHARSET={$this->_charset}";

        try{
            $result = M()->execute($create_table_sql);
            return $result;
        } catch (\PDOException $ex) {
            $this->_error = $ex->getMessage();
            return false;
        }
    }
}

