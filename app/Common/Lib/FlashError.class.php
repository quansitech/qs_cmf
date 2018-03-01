<?php
namespace Common\Lib;

class FlashError{

    public static function all(){
        $errors = Flash::get('qs_flash_error');
        return $errors;
    }

    public static function set($error_msg){
        $errors = Flash::get('qs_flash_error');
        if(is_array($errors)){
            $errors.push($error_msg);
        }
        else{
            $errors = [$error_msg];
        }
        Flash::set('qs_flash_error', $errors);
    }
}
?>
