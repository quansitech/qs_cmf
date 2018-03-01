<?php
namespace Common\Lib;

class Flash{

    public static function set($key, $value){
        session($key, $value);
    }

    public static function get($key, $default = null){
        if(session('?' . $key)){
            $v = session($key);
            session($key, null);
            return $v;
        }
        else{
            return $default;
        }
    }
}

 ?>
