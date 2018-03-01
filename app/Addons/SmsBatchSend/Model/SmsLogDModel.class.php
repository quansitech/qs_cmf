<?php
namespace Addons\SmsBatchSend\Model;
use Gy_Library\GyListModel;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SmsLogDModel
 *
 * @author 英乐
 */
class SmsLogDModel extends GyListModel{
    //put your code here
    protected $_validate = array(
        array('hid', 'require', '缺少hid'),
        array('mobile', 'require', '缺少mobile'),
    );
    
    public function getSuccessNum($hid = ''){
        if($hid != ''){
            $map['hid'] = $hid;
        }
        $map['status'] = '发送成功';
        $count = $this->where($map)->count();
        return $count ? $count : 0;
    }
    
    public function getFailNum($hid = ''){
        if($hid != ''){
            $map['hid'] = $hid;
        }
        $map['status'] = '发送失败';
        $count = $this->where($map)->count();
        return $count ? $count : 0;
    }
    
    public function getUnsendNum($hid = ''){
        if($hid != ''){
            $map['hid'] = $hid;
        }
        $map['status'] = '等待发送';
        $count = $this->where($map)->count();
        return $count ? $count : 0;
    }
}
