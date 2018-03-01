<?php
namespace Addons\SmsBatchSend\Model;
use Gy_Library\GyListModel;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SmsLogHModel
 *
 * @author 英乐
 */
class SmsLogHModel extends GyListModel{
    protected $_validate = array(
        array('body', 'require', '短信内容必填')
    );
    
    protected $_delete_auto = array( 
        array('delete',  'SmsLogD', array('id' => 'hid')),
    );
}
