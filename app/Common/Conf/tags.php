<?php

return array(
	'app_begin'    =>  array('Behaviors\\LoadDBConfigBehavior'),
    'action_begin' =>  array('Behavior\CheckLangBehavior', 'Behaviors\\CheckThemeBehavior'),
    'view_filter'  =>  array('Behavior\\TokenBuildBehavior'),
    'app_init'=>array('Behaviors\\InitHookBehavior','Behaviors\\AppInitBehavior'),
    'template_filter'  =>  array('Behaviors\\TemplateSectionBehavior'),
    'after_home_instance' => array('Behaviors\\WxAutoLoginBehavior'),
    'reset_rbac' => array('Behaviors\\ResetRbacBehavior'),
);
