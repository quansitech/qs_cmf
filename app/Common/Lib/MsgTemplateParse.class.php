<?php

namespace Common\Lib;

class MsgTemplateParse{
    
    static private $_var_func = array(
        '孩子姓名' => 'parseChildName',
        '孩子称呼' => 'parseChildPrefix',
        '志愿者姓名' => 'parseVolunteerName',
        '志愿者称呼' => 'parseVolunteerPrefix',
        '发信时间' => 'parseSendLetterTime'
    );
    
    static public function parse($args, $content){
        while(preg_match('/\[#(.+?)#\]/i', $content, $matches)){
            $key = $matches[1];
            
            $func = self::$_var_func[$key];
            
//            if(!$func){
//                $func = self::$_var_order_process_func[$key];
//            }
            
            $parse_value = call_user_func(__NAMESPACE__ .'\MsgTemplateParse::'.$func, $args);
            $content = str_replace('[#'.$key.'#]', $parse_value, $content);
            $matches = array();
        }
        return $content;
    }
    
    static public function parseChildName($args){
        $cid = $args['cid'];
        $ent = D('Children')->getOne($cid);
        return $ent['name'];
    }
    
    static public function parseChildPrefix($args){
        $cid = $args['cid'];
        $ent = D('Children')->getOne($cid);
        return $ent['gender'] == 'female' ? '妹妹' : '弟弟';
    }
    
    static public function parseVolunteerName($args){
        $vid = $args['vid'];
        $ent = D('Volunteer')->getOne($vid);
        return $ent['name'];
    }
    
    static public function parseVolunteerPrefix($args){
        $vid = $args['vid'];
        $ent = D('Volunteer')->getOne($vid);
        return $ent['gender'] == 'female' ? '姐姐' : '哥哥';
    }
    
    static public function parseSendLetterTime($args){
        $letter_id = $args['letter_id'];
        $ent = D('Letter')->getOne($letter_id);
        return date('Y-m-d H:i:s', $ent['create_date']);
    }
}

