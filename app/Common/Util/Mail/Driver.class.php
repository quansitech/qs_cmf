<?php

namespace Common\Util\Mail;

abstract class Driver {
    
    abstract public function init($param);
    
    abstract public function sendMail($param);
}
