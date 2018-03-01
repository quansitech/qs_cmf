<?php

namespace Addons\AutoCrop;
use Addons\Addon;

class AutoCropAddon extends Addon{
    
    public $info = array(
        'name' => 'AutoCrop',
        'title' => '图片自动裁剪',
        'description' => '自动裁剪上传至网站的图片',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.1'
    );
    
    public $custom_config = 'setting';
    
    public function install(){
        $this->createHook('afterUpload', '文件上传后');
        return true;
    }
    
    public function uninstall(){
        $this->delHook('afterUpload');
        return true;
    }
    
//    public function displayConfig(){
//        $this->assign('meta_title', '设置插件-' . $this->info['title']);
//        $this->display($this->custom_config);
//    }
    
    //IMAGE_THUMB_SCALE     =   1 ; //等比例缩放类型
    //IMAGE_THUMB_FILLED    =   2 ; //缩放后填充类型
    //IMAGE_THUMB_CENTER    =   3 ; //居中裁剪类型
    //IMAGE_THUMB_NORTHWEST =   4 ; //左上角裁剪类型
    //IMAGE_THUMB_SOUTHEAST =   5 ; //右下角裁剪类型
    //IMAGE_THUMB_FIXED     =   6 ; //固定尺寸缩放类型
    public function afterUpload(&$para){
        $save_path = $para[0];
        $file_name = $para[1];
        
        $config = $this->getConfig();
        
        $config['config'] = json_decode(html_entity_decode($config['config']), true);
        
        $image = new \Think\Image();
        $img_file = $save_path . $file_name;
        if(!isImage($img_file)){
            return false;
        }
        
        foreach ($config['config'] as $k => $v){
            $thumb_dir = $save_path . $k . '_' . $file_name;
            $result[] = $image->open($img_file)->thumb($v[0], $v[1], $v[2])->save($thumb_dir);
        }
        return $result;
    }
}

