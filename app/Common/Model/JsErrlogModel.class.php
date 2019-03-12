<?php
/**
 * Created by PhpStorm.
 * User: 95869
 * Date: 2019/3/12
 * Time: 9:25
 */

namespace Common\Model;


use Gy_Library\GyListModel;

class JsErrlogModel extends GyListModel
{
    protected $_auto=[
        ['create_date','time',self::MODEL_INSERT,'function']
    ];
}