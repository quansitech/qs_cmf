<?php
namespace Addons\C123\Util;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of C123
 *
 * @author 英乐
 */
class C123 {
    //put your code here
    
    
    public static function postSMS($url,$data=''){
            $row = parse_url($url);
            $host = $row['host'];
            $port = $row['port'] ? $row['port']:80;
            $file = $row['path'];
            foreach ($data as $k=>$v)
            {
                    $post .= rawurlencode($k)."=".rawurlencode($v)."&";	//转URL标准码
            }
            $post = substr( $post , 0 , -1 );
            $len = strlen($post);
            $fp = @fsockopen( $host ,$port, $errno, $errstr, 10);
            if (!$fp) {
                    return "$errstr ($errno)\n";
            } else {
                    $receive = '';
                    $out = "POST $file HTTP/1.0\r\n";
                    $out .= "Host: $host\r\n";
                    $out .= "Content-type: application/x-www-form-urlencoded\r\n";
                    $out .= "Connection: Close\r\n";
                    $out .= "Content-Length: $len\r\n\r\n";
                    $out .= $post;		
                    fwrite($fp, $out);
                    while (!feof($fp)) {
                            $receive .= fgets($fp, 128);
                    }
                    fclose($fp);
                    $receive = explode("\r\n\r\n",$receive);
                    unset($receive[0]);
                    return implode("",$receive);
            }
    }
}
