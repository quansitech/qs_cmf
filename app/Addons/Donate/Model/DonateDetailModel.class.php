<?php
namespace Addons\Donate\Model;
use \Addons\Donate\DonateCont;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DonateDetailModel
 *
 * @author tider
 */
class DonateDetailModel extends \Gy_Library\GyListModel{
    protected $_validate = array(
        array('header_id', 'require', '缺少header_id'),
        array('line_no', array(1,255), 'line_no超出合理范围', parent::MUST_VALIDATE, 'between', parent::MODEL_BOTH),
        array('amount', array(0,99999999), '捐赠金额超出合理范围', parent::MUST_VALIDATE, 'between', parent::MODEL_BOTH),
        array('amount', 0, '捐赠金额不能为零', parent::MUST_VALIDATE, 'notequal', parent::MODEL_BOTH),
        array('ref_key',array('donateProject', 'donateActivity'),'{%STATUS_OUT_OF_RANGE}',parent::MUST_VALIDATE, 'in', parent::MODEL_BOTH),
        array('ref_key,ref_id', 'checkRelyId', '不合法数据', parent::MUST_VALIDATE, 'callback', parent::MODEL_BOTH),
    );
    
    public function checkRelyId($args){
        $ref_key = $args['ref_key'];
        $ref_id = $args['ref_id'];

        $model_name = ucfirst($ref_key);
        $model = D($model_name);
        $ent = $model->where('id=' . $ref_id)->find();
        return !$ent ? false : true;
    }
    
    public function getDetailByHeaderId($header_id){
        $map['header_id'] = $header_id;
        return $this->where($map)->select();
    }
}
