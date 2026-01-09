<?php

return [
    'action_begin' =>  ['Behavior\CheckLangBehavior', 'Behaviors\\CheckThemeBehavior'],
    'view_filter'  =>  ['Behavior\\TokenBuildBehavior'],
    'app_init'=>['Behaviors\\AppInitBehavior'],
    'template_filter'  =>  ['Behaviors\\TemplateSectionBehavior'],
    'after_home_instance' => ['Behaviors\\WxAutoLoginBehavior']
];
