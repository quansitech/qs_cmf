<?php
namespace Common\Builder;


use Think\View;

class SubTableBuilder{

    private $_table_header = [];
    private $_items = [];
    private $_template;
    private $_unique_id;
    private $_data;
    private $_readonly;
    private $_set_add_btn;

    public function __construct($readonly=false){
        $this->_template = APP_PATH.'Common/Builder/subTableBuilder.html';
        $this->_unique_id = uniqueId();
        $this->_readonly=$readonly;
    }

    public function addTableHeader($name, $width){
        $header['name'] = $name;
        $header['width'] = $width;
        $this->_table_header[] = $header;
        return $this;
    }

    public function addFormItem($name, $type, $options = [],$readonly=false,$extra_class='',$extra_attr='') {
        $item['name'] = $name;
        $item['type'] = $type;
        $item['options'] = $options;
        $item['readonly'] = $readonly;
        $item['extra_class'] = $extra_class;
        $item['extra_attr'] = $extra_attr;
        $this->_items[] = $item;
        return $this;
    }

    public function setData($data){
        $this->_data = $data;
        return $this;
    }

    public function setAddBtn($set_add_btn){
        $this->_set_add_btn = $set_add_btn;
        return $this;
    }

    public function makeHtml(){
        $view = new View();
        $view->assign('table_header', $this->_table_header);
        $view->assign('items', $this->_items);
        $view->assign('table_id', $this->_unique_id);
        $view->assign('data', $this->_data);
        $view->assign('readonly', $this->_readonly);
        $view->assign('set_add_btn', $this->_set_add_btn);

        return $view->fetch($this->_template);
    }
}