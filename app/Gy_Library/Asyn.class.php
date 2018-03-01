<?php
namespace Gy_Library;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Asyn{
    private $_method = 'GET';
    private $_parm;
    private $_host;
    private $_path;
    
    public function run($url,$method = '',$parm = ''){
        if($method != ''){
            $this->_method = $method;
        }
        if($parm != ''){
            $this->_parm = $parm;
        }
        
        $result = parse_url($url);
        $this->_host = $result['host'];
        $this->_path = $result['path'];
        $fp = fsockopen($result['host'], 80, $errno, $errstr, 30);
        if(!$fp){
            \Think\Log::write("{$errstr} ({$errno})");
        }
        else{
            //$out = "{$method} {$result['path']}";
            $out = $this->_buildRequest();
            fwrite($fp, $out);
//            $str = '';
//            while (!feof($fp)) {
//                $str .= fgets($fp, 128);
//            }
            fclose($fp);
        }
    }
    
    private function _buildRequest(){
        if(strtolower($this->_method) == 'get'){
            $gets = $this->_parm;
        }
        
        if(strtolower($this->_method) == 'post'){
            $posts = $this->_parm;
        }
        
        if ( is_array( $gets ) ) { 
            $getValues = '?'; 
            foreach( $gets AS $name => $value ){ 
                $getValues .= urlencode( $name ) . "=" . urlencode( $value ) . '&'; 
            } 
            $getValues = substr( $getValues, 0, -1 ); 
        } 
        else { 
            $getValues = ''; 
        } 

        if ( is_array( $posts ) ) { 
            foreach( $posts AS $name => $value ){ 
                $postValues .= urlencode( $name ) . "=" . urlencode( $value ) . '&'; 
            } 
            $postValues = substr( $postValues, 0, -1 ); 
        } 
        else { 
            $postValues = ''; 
        } 
        $method = strtoupper($this->_method);
        
        $request = "{$method} {$this->_path}{$getValues}  HTTP/1.1\r\n";
        $request .= "Host: {$this->_host}\r\n";
        $request .= "Connection: Close\r\n";

        if ( strtolower($this->_method) == "post" ) { 
            $lenght = strlen( $postValues ); 
            $request .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
            $request .= "Content-Length: $lenght\r\n"; 
            $request .= "\r\n"; 
            $request .= $postValues; 
        } 
        else{
            $request .= "\r\n";
        }
        return $request;
    }
    
    
}
