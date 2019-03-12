<?php
/**
 * Created by PhpStorm.
 * User: 95869
 * Date: 2019/3/12
 * Time: 9:27
 */

namespace Api\Controller;


use Think\Controller;

class JsLogController extends Controller
{
    public function index(){
        if (C('JS_ERROR_LOG')){
            $data=I('get.');
            C('TOKEN_ON',false);
            $r=D('JsErrlog')->createAdd($data);
            $r===false && $this->error(D('JsErrlog')->getError());
        }
        echo '';
    }
}