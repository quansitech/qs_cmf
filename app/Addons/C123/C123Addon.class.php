<?php
namespace Addons\C123;
use Addons\Addon;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of C123Addon
 *
 * @author 英乐
 */
class C123Addon extends Addon{
    //put your code here
    
    public $open_api_url = 'http://smsapi.c123.cn/OpenPlatform/OpenApi';
    
    public $data_api_url = 'http://smsapi.c123.cn/DataPlatform/DataApi';
    
    public $info = array(
        'name' => 'C123',
        'title' => '中国短信网短信接口',
        'description' => '中国短信网短信发送接口集',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.1'
    );
    
    private $_error_code = array(
        0 => "帐户格式不正确(正确的格式为:员工编号@企业编号)",
        -1 => "服务器拒绝(速度过快、限时或绑定IP不对等)如遇速度过快可延时再发",
        -2 => "密钥不正确",
        -3 => "密钥已锁定",
        -4 => "参数不正确(内容和号码不能为空，手机号码数过多，发送时间错误等)",
        -5 => "无此帐户",
        -6 => "帐户已锁定或已过期",
        -7 => "帐户未开启接口发送",
        -8 => "不可使用该通道组",
        -9 => "帐户余额不足",
        -10 => "内部错误",
        -11 => "扣费失败"
    );
    
    private $_return_code = array(
        'DELIVRD' => '短消息转发成功',
        'EXPIRED' => '短消息超过有效期',
        'DELETED' => '短消息已经被删除',
        'UNDELIV' => '短消息是不可达的',
        'ACCEPTD' => '短消息在等待发送中',
        'UNKNOWN' => '未知短消息状态',
        'REJECTD' => '短消息被短信中心拒绝',
        'DTBLACK' => '目的号码是黑名单号码',
        'DTWORDS' => '发送内容被过滤',
        'DTFAILD' => '发送失败原因未明',
        'ERRBUSY' => '运营商系统忙状态未知'
    );
    
    
    public function install(){
        $this->createHook('sendSmsOnce', '发送单条短信');
        $this->createHook('sendSmsBatch', '发送批量短信');
        $this->createHook('getSmsBalance', '获取短信账号余额');
        $this->createHook('getSmsStatus', '获取短信状态');
        $this->createHook('getSmsRely', '获取短信回复');
        return true;
    }
    
    public function uninstall(){
        $this->delHook('sendSmsOnce');
        $this->delHook('sendSmsBatch');
        $this->delHook('getSmsBalance');
        $this->delHook('getSmsStatus');
        $this->delHook('getSmsRely');
        return true;
    }
    
    public function getSmsBalance(&$para){
        $config = $this->getConfig();
        
        $data = array(
            'action'=>'getBalance',                                //发送类型 ，可以有sendOnce短信发送，sendBatch一对一发送，sendParam	动态参数短信接口
            'ac'=> C('C123_ac'),					                         //用户账号
            'authkey'=> C('C123_authkey'),	                             //认证密钥
        );
        $xml= Util\C123::postSMS($this->open_api_url,$data);			                     //POST方式提交
        $re=simplexml_load_string(utf8_encode($xml));
        if(trim($re['result'])==1){
	    foreach ($re->Item as $item){
                $stat['remain']=trim((string)$item['remain']);
                $stat_arr[]=$stat;
			
            }
            $return['status'] = 1;
            $return['data'] = $stat_arr;
		
        }
	else{
            $return['status'] = 0;
            $return['err_msg'] = $this->_error_code[trim($re['result'])];
	}
        $para['return'] = $return;
    }
    
    public function sendSmsBatch(&$para){
        $config = $this->getConfig();
        
        $content = $para['content'];
        $mobile = $para['mobile'];

        $data = array(
            'action'=>'sendBatch',                                //发送类型 ，可以有sendOnce短信发送，sendBatch一对一发送，sendParam	动态参数短信接口
            'ac'=>C('C123_ac'),					                         //用户账号
            'authkey'=>C('C123_authkey'),	                             //认证密钥
            'cgid'=>C('C123_cgid'),                                       //通道组编号
            'm'=>$mobile,		                                     //号码,多个号码用逗号隔开
            'c'=>$content,		                 //如果页面是gbk编码，则转成utf-8编码，如果是页面是utf-8编码，则不需要转码
            'csid'=>C('C123_csid'),                                       //签名编号 ，可以为空，为空时使用系统默认的签名编号
            't'=> ''                                              //定时发送，为空时表示立即发送
        );
	$xml= Util\C123::postSMS($this->open_api_url,$data);			                     //POST方式提交
        $re=simplexml_load_string(utf8_encode($xml));
	if(trim($re['result'])==1){
	    foreach ($re->Item as $item){
			 
                $stat['msgid'] =trim((string)$item['msgid']);
                $stat['total']=trim((string)$item['total']);
                $stat['price']=trim((string)$item['price']);
                $stat['remain']=trim((string)$item['remain']);
                $stat_arr[]=$stat;
			
            }
            $return['status'] = 1;
            $return['data'] = $stat_arr;
		
        }
	else{
            $return['status'] = 0;
            $return['err_msg'] = $this->_error_code[trim($re['result'])];
	}
        $para['return'] = $return;
    }
    
    public function sendSmsOnce(&$para){
        $config = $this->getConfig();
        
        $content = $para['content'];
        $mobile = $para['mobile'];

        $data = array(
            'action'=>'sendOnce',                                //发送类型 ，可以有sendOnce短信发送，sendBatch一对一发送，sendParam	动态参数短信接口
            'ac'=>$config['ac'],					                         //用户账号
            'authkey'=>$config['authkey'],	                             //认证密钥
            'cgid'=>$config['cgid'],                                       //通道组编号
            'm'=>$mobile,		                                     //号码,多个号码用逗号隔开
            'c'=>$content,		                 //如果页面是gbk编码，则转成utf-8编码，如果是页面是utf-8编码，则不需要转码
            'csid'=>$config['csid'],                                       //签名编号 ，可以为空，为空时使用系统默认的签名编号
            't'=> ''                                              //定时发送，为空时表示立即发送
        );
	$xml= Util\C123::postSMS($this->open_api_url,$data);			                     //POST方式提交
        $re=simplexml_load_string(utf8_encode($xml));
	if(trim($re['result'])==1){
	    foreach ($re->Item as $item){
			 
                $stat['msgid'] =trim((string)$item['msgid']);
                $stat['total']=trim((string)$item['total']);
                $stat['price']=trim((string)$item['price']);
                $stat['remain']=trim((string)$item['remain']);
                $stat_arr[]=$stat;
			
            }
            $return['status'] = 1;
            $return['data'] = $stat_arr;
		
        }
	else{
            $return['status'] = 0;
            $return['err_msg'] = $this->_error_code[trim($re['result'])];
	}
        $para['return'] = $return;
    }
    
    public function getSmsStatus(&$para){
        $config = $this->getConfig();
        
        $data = array(
            'action'=>'getSendState',               //发送类型 ，可以有sendOnce短信发送，sendBatch一对一发送，sendParam	动态参数短信接口
            'ac'=>C('C123_ac'),				//用户账号
            'authkey'=>C('C123_authkey'),	                //认证密钥
        );
        $xml= Util\C123::postSMS($this->data_api_url,$data);	//POST方式提交
        $re=simplexml_load_string(utf8_encode($xml));
        if(trim($re['result'])==1){
	    foreach ($re->Item as $item){
                $stat['result'] = trim((string)$item['result']);
                $stat['msgid'] = trim((string)$item['msgid']);
                $stat['msg'] = $this->_return_code[trim((string)$item['return'])];
                $stat_arr[]=$stat;
			
            }
            $return['status'] = 1;
            $return['data'] = $stat_arr;
            
        }
        else{
            $return['status'] = 0;
            $return['err_msg'] = $this->_error_code[$re['result']];
        }
        $para['return'] = $return;
        
    }
    
    public function getSmsRely(&$para){
        $config = $this->getConfig();
        
        $data = array(
            'action'=>'getReply',               //发送类型 ，可以有sendOnce短信发送，sendBatch一对一发送，sendParam	动态参数短信接口
            'ac'=>C('C123_ac'),				//用户账号
            'authkey'=>C('C123_authkey'),	                //认证密钥
        );
        $xml= Util\C123::postSMS($this->data_api_url,$data);	//POST方式提交
        $re=simplexml_load_string($xml);
        if(trim($re['result'])==1){
	    foreach ($re->Item as $item){
                $stat['msgid'] = trim((string)$item['msgid']);
                $stat['content'] = trim((string)$item['content']);
                $stat_arr[]=$stat;
			
            }
            $return['status'] = 1;
            $return['data'] = $stat_arr;
            
        }
        else{
            $return['status'] = 0;
            $return['err_msg'] = $this->_error_code[$re['result']];
        }
        $para['return'] = $return;
        
    }
    
    
}
