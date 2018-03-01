<?php
namespace Addons\Stat\Model;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StatModel
 *
 * @author tider
 */
class StatModel extends \Gy_Library\GyListModel{
    //put your code here
    
    public function getViewNum(){
        $map['type'] = \Addons\Stat\StatCont::WEBSITE_VIEW;
        $view_num = $this->where($map)->sum('num');
        return $view_num == '' ? 0 : $view_num;
    }
    
    public function getArticleViewNum($ref_id, $ref_key){
        $map['ref_id'] = $ref_id;
        $map['ref_key'] = $ref_key;
        $map['type'] = \Addons\Stat\StatCont::ARTICLE_VIEW;
        
        $num = $this->where($map)->getField('num');
        
        return $num == '' ? 0 : $num;
    }
    
    public function getVistorNum(){
        $map['type'] = \Addons\Stat\StatCont::WEBSITE_VIEW;
        $vistor_num = $this->where($map)->count();
        return $vistor_num == '' ? 0 : $vistor_num;
    }
    
    public function getDownloadNum($file_id = ''){
        if(empty($file_id)){
            $download_num = $this->where(array('type' => \Addons\Stat\StatCont::FILE_DOWNLOAD))->sum('num');
            return $download_num == '' ? 0 : $download_num;
        }
        else{
            $map['type'] = \Addons\Stat\StatCont::FILE_DOWNLOAD;
            $map['ref_key'] = 'FilePic';
            $map['ref_id'] = $file_id;
            $file_num = $this->where($map)->getField('num');
            return $file_num == '' ? 0 : $file_num;
        }
    }
    
    public function getThumbUpNum($ref_id = '', $ref_key = ''){
        if(empty($ref_id)){
            $thumb_up_num = $this->where(array('type' => \Addons\Stat\StatCont::THUMBS_UP))->sum('num');
            return $thumb_up_num == '' ? 0 : $thumb_up_num;
        }
        else{
            $map['type'] = \Addons\Stat\StatCont::THUMBS_UP;
            $map['ref_id'] = $ref_id;
            $map['ref_key'] = $ref_key;
            $o_thumb_up_num = $this->where($map)->getField('num');
            return $o_thumb_up_num == '' ? 0 : $o_thumb_up_num;
        }
    }
    
    public function getShareNum($ref_id = '', $ref_key = ''){
        if(empty($ref_id)){
            $share_num = $this->where(array('type'=> \Addons\Stat\StatCont::SHARE))->sum('num');
            return $share_num == '' ? 0 : $share_num;
        }
        else{
            $map['type'] = \Addons\Stat\StatCont::SHARE;
            $map['ref_id'] = $ref_id;
            $map['ref_key'] = $ref_key;
            $o_share_num = $this->where($map)->getField('num');
            return $o_share_num == '' ? 0 : $o_share_num;
        }
    }
    
    public function getRefTitle($id){
        $ent = $this->where('id='. $id)->find();
        $ref_ent = D(ucfirst($ent['ref_key']))->where('id='. $ent['ref_id'])->find();
        if(!$ref_ent){
            return false;
        }
        return isset($ref_ent['title']) ? $ref_ent['title'] : '';
    }
    
    
}
