<?php
namespace Api\Controller;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * @deprecated oss上传功能已在扩展包quansitech/qscmf-formitem-aliyun-oss中实现
 **/
class OssUploadController extends \Think\Controller{

    public function callBack(){
        $r = $this->_verify($body);
        if($r === false){
            exit();
        }

        parse_str($body, $body_arr);
        $config = C('UPLOAD_TYPE_' . strtoupper($body_arr['upload_type']));
        if(!$config){
            E('获取不到文件规则config设置');
        }

        if(!empty($config['mimes'])){
            $mimes = explode(',', $config['mimes']);
            if(!in_array(strtolower($body_arr['mimeType']), $mimes)){
                $this->ajaxReturn(array('err_msg' => '请上传图片'));
            }
        }

        $file_data['url'] = $config['oss_host'] . '/' . $body_arr['filename'] . ($config['oss_style'] ? $config['oss_style'] : '');
        $file_data['size'] = $body_arr['size'];
        $file_data['cate'] = $body_arr['upload_type'];
        $file_data['security'] = $config['security'] ? 1 : 0;

        C('TOKEN_ON',false);
        $r = D('FilePic')->createAdd($file_data);
        if($r === false){
            E(D('FilePic')->getError());
        }
        else{
            if($file_data['security'] == 1){
                $ali_oss = new \Common\Util\AliOss();
                $file_data['url'] = $ali_oss->getOssClient($body_arr['upload_type'])->signUrl($body_arr['filename'], 60);
            }
            $this->ajaxReturn(array('file_id' => $r, 'file_url' => $file_data['url']));
        }
    }

    public function policyGet($type){
        $callbackUrl = HTTP_PROTOCOL . '://' . SITE_URL . '/api/OssUpload/callBack';

        $callback_param = array('callbackUrl'=>$callbackUrl,
                 'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&upload_type=${x:upload_type}',
                 'callbackBodyType'=>"application/x-www-form-urlencoded");
        $callback_string = json_encode($callback_param);
        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 10;
        $end = $now + $expire;
        $expiration = gmt_iso8601($end);

        $config = C('UPLOAD_TYPE_' . strtoupper($type));
//        $sub_name = $this->_getName($config['subName']);
//        $pre_path = $config['rootPath'] . $config['savePath'] . $sub_name .'/';
//        $save_name = $this->_getName($config['saveName']);
//
//        $dir = trim(trim($pre_path . $save_name, '.'), '/');

        $dir = \Common\Util\AliOss::genOssObjectName($config);
        $condition = array(0=>'content-length-range', 1=>0, 2=> $config['maxSize']);

        $conditions[] = $condition;

        $start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
        $conditions[] = $start;

        $arr = array('expiration'=>$expiration,'conditions'=>$conditions);

        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, C('ALIOSS_ACCESS_KEY_SECRET'), true));

        $callback_var = json_encode(array('x:upload_type' => $type));

        $response = array();
        $response['accessid'] = C('ALIOSS_ACCESS_KEY_ID');
        $response['host'] = $config['oss_host'];
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        $response['callback'] = $base64_callback_body;
        $response['callback_var'] = $callback_var;
        if($config['oss_meta']){
            $response['oss_meta'] = json_encode($config['oss_meta']);
        }
        //这个参数是设置用户上传指定的前缀
        $response['dir'] = $dir;
        $this->ajaxReturn($response);
    }

    private function _verify(&$body){
        $authorizationBase64 = "";
        $pubKeyUrlBase64 = "";

        if (isset($_SERVER['HTTP_AUTHORIZATION']))
        {
            $authorizationBase64 = $_SERVER['HTTP_AUTHORIZATION'];
        }
        if (isset($_SERVER['HTTP_X_OSS_PUB_KEY_URL']))
        {
            $pubKeyUrlBase64 = $_SERVER['HTTP_X_OSS_PUB_KEY_URL'];
        }

        if ($authorizationBase64 == '' || $pubKeyUrlBase64 == '')
        {
            return false;
        }

        $authorization = base64_decode($authorizationBase64);
        $pubKeyUrl = base64_decode($pubKeyUrlBase64);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pubKeyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $pubKey = curl_exec($ch);

        if ($pubKey == "")
        {
            return false;
        }

        $body = file_get_contents('php://input');
        $authStr = '';
        $path = $_SERVER['REQUEST_URI'];
        $pos = strpos($path, '?');

        if ($pos === false)
        {
            $authStr = urldecode($path)."\n".$body;
        }
        else
        {
            $authStr = urldecode(substr($path, 0, $pos)).substr($path, $pos, strlen($path) - $pos)."\n".$body;
        }

        $ok = openssl_verify($authStr, $authorization, $pubKey, OPENSSL_ALGO_MD5);
        if ($ok == 1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }


}
