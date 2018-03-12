<?php
namespace Common\Util\RSA;
/**
 * Created by PhpStorm.
 * User: quansi
 * Date: 2017/12/21
 * Time: 18:10
 */

class RSA{

    private static $_private_key = "私钥";

    public static function decrypt($data){
        $encrypted = base64_decode($data);
        $r = openssl_private_decrypt($encrypted, $encrypted, openssl_pkey_get_private(self::$_private_key));
        if($r === false){
            E('rsa decrypt fail');
        }
        return $encrypted;
    }
}
