<?php
namespace Gy_Library;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DES{
    private $_td;
    private $_iv;
    private $_ks;
    
    public function __construct() {
        $this->_td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_ECB, '');
        $size = mcrypt_enc_get_iv_size($this->_td);
        $this->_iv = mcrypt_create_iv($size, MCRYPT_RAND);
        $this->_ks = mcrypt_enc_get_key_size($this->_td);
    }
    
    public function encode($key, $data){
        $key = substr($key, 0, $this->_ks);
        mcrypt_generic_init($this->_td, $key, $this->_iv);
        return base64_encode(mcrypt_generic($this->_td, $data));
    }
    
    public function decode($key, $data){
        $key = substr($key, 0, $this->_ks);
        mcrypt_generic_init($this->_td, $key, $this->_iv);
        return mdecrypt_generic($this->_td, base64_decode($data));
    }
    
    public function __destruct() {
        mcrypt_generic_deinit($this->_td);
        mcrypt_module_close($this->_td);
    }
}