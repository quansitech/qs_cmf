<?php
namespace Addons\Note;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NoteCont
 *
 * @author tider
 */
class NoteCont {
    //put your code here
    
    //const NOTE_TYPE_RELY = 1;
    const NOTE_TYPE_MSG = 3;
    
    static private $_note_type = array(
        //self::NOTE_TYPE_RELY => '回复我的',
        self::NOTE_TYPE_MSG => '系统消息'
    );
    
    static function getNoteType($type){
        return self::$_note_type[$type];
    }
    
    static function getNoteTypeList(){
        return self::$_note_type;
    }
}
