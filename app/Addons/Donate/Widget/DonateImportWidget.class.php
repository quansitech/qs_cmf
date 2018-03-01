<?php

namespace Addons\Donate\Widget;
use Think\Controller;

class DonateImportWidget extends Controller{
    
    public $columns_setting = array(
        array('column_name' => '捐赠时间', 'db_field' => 'donate_date', 'type' => 'text'),
        array('column_name' => '捐赠人', 'db_field' => 'name', 'type' => 'text'),
        array('column_name' => '金额', 'db_field' => 'total_amount', 'type' => 'text'),
        array('column_name' => '捐赠类型', 'db_field' => 'ref_key', 'type'=>'select', 'values' => array('donateProject'=>'单次捐赠', 'donateActivity'=>'活动捐赠')),
        array('column_name' => '捐赠名称', 'db_field' => 'ref_id', 'type'=>'select', 'callback'=> array('function_name' => '_parseRef')),
        array('column_name' => '捐赠渠道', 'db_field' => 'channel_id', 'type'=>'select', 'callback'=>array('function_name' => '_getPayChannelList')),
        array('column_name' => '备注', 'db_field' => 'remark', 'type'=>'text')
    );
    
    private $_current_row_value;
    private $_current_row;
    
    public function index($import_file){
        echo ob_get_clean();
        
        $gy_excel = new \Gy_Library\GyExcel();
        if(strpos($import_file, '.xlsx') === false && strpos($import_file, '.xls') === false){
            flushWebContent('不是excel文件');
            exit();
        }

        $php_excel = $gy_excel->load($import_file);
        $column_count = count($this->columns_setting);

        $row = 1;
        $list = $gy_excel->readRow($php_excel, $row, $column_count);
        while(!empty($list[0])){
            $this->_current_row_value = $list;
            $this->_current_row = $row - 2;
            if($row == 1 && $this->_checkHeader($list) === false){
                flushWebContent($this->_echoError('标题名称不一致'));
                break;
            }
            if($list === false){
                flushWebContent($this->_echoError($gy_excel->getError()));
                break;
            }
            if($row == 1){
                 foreach($list as $k=>$v){
                    $html .= '<td>' . $v . '</td>';
                }
                $html .= '</tr>';
                flushWebContent($html);
                $row++;
                $list = $gy_excel->readRow($php_excel, $row, $column_count);
                continue;
            }
            
            $html = '<tr>';
            foreach($list as $k=>$v){
                $html .= '<td>' . $this->_parseInput($k, $v) . '</td>';
            }
            $html .= '</tr>';
            flushWebContent($html);
            $row++;
            $list = $gy_excel->readRow($php_excel, $row, $column_count);
        }
    }
    
    private function _checkHeader($header_list){
        $k = 0;
        foreach($header_list as $v){
            $column_name = $this->columns_setting[$k]['column_name'];
            if($column_name != $v){
                return false;
            }
            $k++;
        }
        return true;
    }
    
    private function _echoError($msg){
        return '<p style="color:red">' . $msg . '</p>';
    }
    
    private function _parseInput($k, $v){
        $input_name = $this->columns_setting[$k]['db_field'];
        $input_type = $this->columns_setting[$k]['type'];
        
        switch ($input_type){
            case 'text';
                $str = "<input id='{$this->_current_row}_{$k}' type='text' name='{$input_name}[]' value='{$v}' >";
                break;
            case 'select';
                $str = "<select name='{$input_name}[]' id='{$this->_current_row}_{$k}'>";
                $str .= "<option value=''>无</option>";
                if(isset($this->columns_setting[$k]['values'])){
                    foreach($this->columns_setting[$k]['values'] as $kk=>$vv){
                        $selected = $vv == $v ? "selected='selected'" : '';
                        $str .= "<option value='{$kk}' {$selected}>{$vv}</option>";
                    }
                }
                else if(isset($this->columns_setting[$k]['callback']) || isset($this->columns_setting[$k]['function'])){
                    $function_arr = isset($this->columns_setting[$k]['callback']) ? $this->columns_setting[$k]['callback'] : $this->columns_setting[$k]['function'];
                    $function_name = $function_arr['function_name'];
                    $args = isset($function_arr['args']) ? $function_arr['args'] : array();
                    $values = isset($this->columns_setting[$k]['callback']) ? call_user_func_array(array(&$this, $function_name), $args) : call_user_func_array($function_name, $args);
                    foreach($values as $kk=>$vv){
                        $selected = $vv == $v ? "selected='selected'" : '';
                        $str .= "<option value='{$kk}' {$selected}>{$vv}</option>";
                    }
                }
                $str .= "</select>";
                break;
            default:
                break;
        }
        
        $str .= "<span id='{$this->_current_row}_{$k}_err'></span>";
        return $str;
    }
    
    private function _parseRef(){
        if($this->_current_row_value[3] == '活动捐赠'){
            $dactivity_ents = D('DonateActivity')->getImportAble();
            $return_list = array();
            foreach($dactivity_ents as $v){
                $return_list[$v['id']] = $v['title'];
            }
            return $return_list;
        }
        else{
            
            $cate_ents = D('DonateProject')->where('status=' . \Gy_Library\DBCont::NORMAL_STATUS)->select();
            $return_list = array();
            foreach($cate_ents as $v){
                $return_list[$v['id']] = $v['title'];
            }
            return $return_list;
        }
    }
    
    private function _getPayChannelList(){
        return \Addons\Donate\DonateCont::getPayChannelList();
    }
    
}