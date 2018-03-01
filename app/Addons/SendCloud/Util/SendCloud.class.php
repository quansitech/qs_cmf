<?php

namespace Addons\SendCloud\Util;

class SendCloud{
    
    private $_url = 'http://sendcloud.sohu.com/webapi/mail.send.json';
    private $_template_url = 'http://sendcloud.sohu.com/webapi/mail.send_template.json';
    private $_unsubscribe_get_url = 'http://sendcloud.sohu.com/webapi/unsubscribes.get.json';
    private $_unsubscribe_del_url = 'http://sendcloud.sohu.com/webapi/unsubscribes.delete.json';
    private $_template_invoke_name = 'test_2';
    private $_batch_api_user = 't4t_batch1';
    private $_api_key = 'APhwrJM7UaLd6LLI';
    private $_reply_to = '';
    //private $_trigger_api_user = 't4t_trigger';
    private $_from = 'service@send.t4tstudio.com';
    private $_from_name;
    
    private $_config;
    
    public function __construct($config) {
        $this->_config = $config;
        $this->_config['url'] = 'http://sendcloud.sohu.com/webapi/mail.send.json';
        $this->_config['template_url'] = 'http://sendcloud.sohu.com/webapi/mail.send_template.json';
        $this->_config['unsubscribe_get_url'] = 'http://sendcloud.sohu.com/webapi/unsubscribes.get.json';
        $this->_config['unsubscribe_del_url'] = 'http://sendcloud.sohu.com/webapi/unsubscribes.delete.json';
    }
    
    function setReplyTo($reply_to){
        $this->_config['reply_to'] = $reply_to;
        return $this;
    }
    
    function setFrom($from){
        $this->_config['from'] = $from;
        return $this;
    }
    
    function setFromName($from_name){
        $this->_config['from_name'] = $from_name;
        return $this;
    }
    
    function sendMail($to, $sub, $headers = '', $files = '') {
        
        if(!is_array($to)){
            $to = explode(',', $to);
        }
        
        $vars = json_encode(array(
                    'to' => $to,
                    'sub' => $sub
                ));

        $param = array(
            'api_user' => $this->_config['batch_api_user'], 
            'api_key' => $this->_config['api_key'],
            'resp_email_id' => 'true',
            'from' => $this->_config['from'], 
            'fromname' => $this->_config['from_name'],
            'substitution_vars' => $vars,
            'template_invoke_name' => $this->_config['template_invoke_name'],
            'replyto' => $this->_config['reply_to'],
            'headers' => $headers,
        );



        $eol = "\r\n";
        $data = '';

        $mime_boundary=md5(time());

        // 配置参数
        foreach ( $param as $key => $value ) { 
            $data .= '--' . $mime_boundary . $eol;  
            $data .= 'Content-Disposition: form-data; '; 
            $data .= "name=" . $key . $eol . $eol; 
            $data .= $value . $eol; 
        }
        
        
        foreach($files as $v){
            $file = $v['file']; #你的附件路径
            $handle = fopen($file,'rb');
            $content = fread($handle,filesize($file));
            // 配置文件
            $data .= '--' . $mime_boundary . $eol;
            $data .= 'Content-Disposition: form-data; name="' . $v['title'] . '"; filename="'.$v['title'].'"' . $eol;
            $data .= 'Content-Type: text/plain' . $eol;
            $data .= 'Content-Transfer-Encoding: binary' . $eol . $eol;
            $data .= $content . $eol;
            fclose($handle);
        }
        $data .= "--" . $mime_boundary . "--" . $eol . $eol; 

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: multipart/form-data;boundary='.$mime_boundary . $eol,
                'content' => $data
        ));
        $context  = stream_context_create($options);
        $result = file_get_contents($this->_config['template_url'], FILE_TEXT, $context);

        
        
        return $result;
    }
    
    
    public function unsubscribeList($start, $limit){
        $param = array(
            'api_user' => $this->_config['batch_api_user'],
            'api_key' => $this->_config['api_key'],
            'start_date' => '2016-01-01',
            'end_date' => date('Y-m-d', time()),
            'api_user_list' => $this->_['batch_api_user'],
            'start' => $start,
            'limit' => $limit
        );
        
        $param = http_build_query($param);
        $result = file_get_contents($this->_config['unsubscribe_get_url'] . '?' . $param);
        return $result;
    }
    
    public function delUnsubscribe($email){
        $param = array(
            'api_user' => $this->_config['batch_api_user'],
            'api_key' => $this->_config['api_key'],
            'email' => $email
        );
        $param = http_build_query($param);
        $result = file_get_contents($this->_config['unsubscribe_del_url'] . '?' . $param);
        return $result;
    }
    
}

