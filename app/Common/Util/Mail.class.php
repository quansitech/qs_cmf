<?php

namespace Common\Util;

class Mail{
    
    public $mailer;
    
    public function __construct() {
        if(!C('MAIL_HOST') || !C('MAIL_PORT') || !C('MAIL_USERNAME')
        || !C('MAIL_PASSWORD')){
            E(l('EMAIL_CONFIG_NULL'));
        }
        
        $class = '\\Common\\Util\\Mail\\Driver\\' . C('MAIL_DRIVER');
        $this->mailer = new $class();
        
        $params = array(C('MAIL_HOST'), C('MAIL_PORT'), C('MAIL_CHARSET'), C('MAIL_ENCODING'), C('MAIL_USERNAME'), C('MAIL_PASSWORD'), C('MAIL_FROM_EMAIL'));
        $this->mailer->init($params);  
    }
    
    //0:from_name, 1:to_mail, 2:cc, 3:bcc, 4:subject, 5:body, 6:attach [path, name]
    public function sendMail($param){
        
        return $this->mailer->sendMail($param);
    }
    
    public function getError(){
        return $this->mailer->ErrorInfo;
    }
}

