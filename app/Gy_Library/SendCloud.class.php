<?php

namespace Gy_Library;

class SendCloud{
    
    private $_url = 'http://sendcloud.sohu.com/webapi/mail.send.json';
    private $_template_url = 'http://sendcloud.sohu.com/webapi/mail.send_template.json';
    private $_unsubscribe_get_url = 'http://sendcloud.sohu.com/webapi/unsubscribes.get.json';
    private $_unsubscribe_del_url = 'http://sendcloud.sohu.com/webapi/unsubscribes.delete.json';
    private $_template_invoke_name;
    private $_batch_api_user;
    private $_api_key;
    private $_reply_to;
    //private $_trigger_api_user = 't4t_trigger';
    private $_from;
    private $_from_name;
    
    public function __construct() {
        $this->_template_invoke_name = C('sendCloud_template_invoke_name');
        $this->_batch_api_user = C('sendCloud_batch_api_user');
        $this->_api_key = C('sendCloud_api_key');
        $this->_from = C('sendCloud_from');
        $this->_from_name = C('sendCloud_from_name');
        $this->_reply_to = C('sendCloud_reply_to');
    }
    
    function setReplyTo($reply_to){
        $this->_reply_to = $reply_to;
        return $this;
    }
    
    function setFrom($from){
        $this->_from = $from;
        return $this;
    }
    
    function setFromName($from_name){
        $this->_from_name = $from_name;
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
            'api_user' => $this->_batch_api_user, 
            'api_key' => $this->_api_key,
            'resp_email_id' => 'true',
            'from' => $this->_from, 
            'fromname' => $this->_from_name,
            'substitution_vars' => $vars,
            'template_invoke_name' => $this->_template_invoke_name,
            'replyto' => $this->_reply_to,
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
        $result = file_get_contents($this->_template_url, FILE_TEXT, $context);

        
        
        return $result;
    }
    
    public function parseSub($content, $template){
        $var_arr = array();
        $template_arr = array();
        //这里写死了内容变量部分必须是<p>%content%</p>的格式，时间不足，暂时使用这种容易出错的方法
//        \Think\Log::write($content);
//        \Think\Log::write($template);
        $temp_template = str_replace('<p>%content%</p>', '%content%', $template);
        while(preg_match('/(%\w+?%)/i', $temp_template, $matches)){
            $key = $matches[1];
            
            $temp_arr = preg_split($key, $temp_template);
//            \Think\Log::write($temp_arr[0]);
            $template_arr[] = $temp_arr[0];
            $var_arr[] = str_replace('%', '', $key);
            //$template = str_replace($key, '(.*?)', $template);
            $temp_template = str_replace_first($temp_arr[0] . $key, '', $temp_template);
        }
//        \Think\Log::write($temp_template);
//        \Think\Log::write(json_encode($var_arr));
        
        $template_arr[] = $temp_template;
        $sub_arr = array();
        for($i = 0; $i < count($template_arr); $i++){
            $content = str_replace_first($template_arr[$i], "{|}", $content);
        }
//        \Think\Log::write($content);
        $content = trim($content, "{|}");
        $arr = explode("{|}", $content);
        for($i = 0; $i < count($var_arr); $i++){
            $sub_arr['%' . $var_arr[$i] . '%'] = array($arr[$i]); 
        }
//        \Think\Log::write(json_encode($sub_arr));
        return $sub_arr;
    }
    
    
    public function unsubscribeList($start, $limit){
        $param = array(
            'api_user' => $this->_batch_api_user,
            'api_key' => $this->_api_key,
            'start_date' => '2016-01-01',
            'end_date' => date('Y-m-d', time()),
            'api_user_list' => $this->_batch_api_user,
            'start' => $start,
            'limit' => $limit
        );
        
        $param = http_build_query($param);
        $result = file_get_contents($this->_unsubscribe_get_url . '?' . $param);
        return $result;
    }
    
    public function delUnsubscribe($email){
        $param = array(
            'api_user' => $this->_batch_api_user,
            'api_key' => $this->_api_key,
            'email' => $email
        );
        $param = http_build_query($param);
        $result = file_get_contents($this->_unsubscribe_del_url . '?' . $param);
        return $result;
    }
    
}
