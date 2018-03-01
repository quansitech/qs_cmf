<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return array(
    'description'=>array(
        'title'=>'插件说明：',
        'type'=>'textarea',
        'value'=>'设置的值必须配对出现,如model设置了2组，则后面所有都必须是2组值,换行作为组分隔符,url 格式如：/Home/DonateActivity/detail/id/__id__, 其中__id__代表该处将被数据表的id字段取代, url中可出现多个待替代值, search_fields的字段用"|"分隔,field_map格式为：title=title|date=publish_date|summary=summary',
    ),
    
    'description1'=>array(
        'title'=>'补充说明：',
        'type'=>'textarea',
        'value'=>'field_map特殊处理手法,有些文章可能有summary,有些可能有content,这种情况可以使用 summary=summary|summary=content的方法来保证获取到摘要,程序已经做了非空处理，保证值不会被空字符串覆盖',
    ),
    
    'model' => array(
        'title'=>'model:',
        'type' => 'textarea',
        'value' => '',
    ),
    
    'model_name' => array(
        'title' => 'model_name',
        'type'=> 'textarea',
        'value' => '',
    ),
    
    'url' => array(
        'title' => 'url',
        'type' => 'textarea',
        'value' => '',
    ),
    
    'search_fields' => array(
        'title' => 'search_fields',
        'type' => 'textarea',
        'value' => '',
    ),
    'field_map' => array(
        'title' => 'field_map',
        'type' => 'textarea',
        'value' => '',
    ),
);

