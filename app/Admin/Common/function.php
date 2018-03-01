<?php
//获取配置类型ID对应的名称
function get_config_type($type=0){
    $list = C('CONFIG_TYPE_LIST');
    return $list[$type];
}

//获取配置分组ID对应的名称
function get_config_group($group=''){
    $list = C('CONFIG_GROUP_LIST');
    return $list[$group];
}

//获取后台的网页标题
function webSiteTitle(){
    $node_name = D('Node')->getNodeName(MODULE_NAME, CONTROLLER_NAME, ACTION_NAME);
    return  $node_name == '' ? C('WEB_SITE_TITLE') : $node_name. ' - ' . C('WEB_SITE_TITLE');
}
