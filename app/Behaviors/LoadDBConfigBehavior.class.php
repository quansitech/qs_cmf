<?php

namespace Behaviors;

class LoadDBConfigBehavior extends \Think\Behavior{
    
     //行为执行入口
    public function run(&$param){
    	readerSiteConfig();
    }
}
