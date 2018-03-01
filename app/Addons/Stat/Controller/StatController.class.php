<?php
namespace Addons\Stat\Controller;
use Home\Controller\AddonsController;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class StatController extends AddonsController{
    
    public function getThumbUp(){
        $ref_id = I('get.ref_id');
        $ref_key = I('get.ref_key');
        $data['thumbUp'] = session("thumb_up_{$ref_key}_{$ref_id}") === true ? 1 : 0;
        
        $this->ajaxReturn($data);
    }
    
    public function thumbUp(){
        $ref_id = I('get.ref_id');
        $ref_key = I('get.ref_key');
        
        if(session("thumb_up_{$ref_key}_{$ref_id}") === true){
            session("thumb_up_{$ref_key}_{$ref_id}", null);
            $param = array(\Addons\Stat\StatCont::THUMBS_UP, -1, $ref_id, $ref_key);
            \Think\Hook::listen('stat', $param);
            
            $data['status'] = 0;
            $data['info'] = '-1';
            $this->ajaxReturn($data);
        }
        else{
            session("thumb_up_{$ref_key}_{$ref_id}", true);
            
            $param = array(\Addons\Stat\StatCont::THUMBS_UP, 1, $ref_id, $ref_key);
            \Think\Hook::listen('stat', $param);
            
            $data['status'] = 1;
            $data['info'] = '+1';
            $this->ajaxReturn($data);
        }
    }
    
    public function getThumbUpNum(){
        $ref_id = I('get.ref_id');
        $ref_key = I('get.ref_key');
        
        echo D('Addons://Stat/Stat')->getThumbUpNum($ref_id, $ref_key);
    }
    
    public function share(){
        $ref_id = I('get.ref_id');
        $ref_key = I('get.ref_key');
            
        $param = array(\Addons\Stat\StatCont::SHARE, 1, $ref_id, $ref_key);
        \Think\Hook::listen('stat', $param);
            
        $data['status'] = 1;
        $data['info'] = '+1';
        $this->ajaxReturn($data);

    }
    
    public function getShare(){
        $ref_id = I('get.ref_id');
        $ref_key = I('get.ref_key');
        
        $map['ref_id'] = $ref_id;
        $map['ref_key'] = $ref_key;
        $map['type'] = \Addons\Stat\StatCont::SHARE;
        $num = D('Addons://Stat/Stat')->where($map)->getField('num');
        
        echo $num == '' ? 0 : $num;
    }
    
    public function articleView(){
        $ref_id = I('get.ref_id');
        $ref_key = I('get.ref_key');
        
        $param = array(\Addons\Stat\StatCont::ARTICLE_VIEW, 1, $ref_id, $ref_key);
        \Think\Hook::listen('stat', $param);
        
        $data['status'] = 1;
        $data['info'] = '+1';
        $this->ajaxReturn($data);
    }
    
    public function getArticleViewNum(){
        $ref_id = I('get.ref_id');
        $ref_key = I('get.ref_key');
        
        echo D('Addons://Stat/Stat')->getArticleViewNum($ref_id, $ref_key);
    }
    
    public function getDownloadNum(){
        $ref_id = I('get.ref_id');
        $ref_key = I('get.ref_key');
        
        $map['ref_id'] = $ref_id;
        $map['ref_key'] = $ref_key;
        $map['type'] = \Addons\Stat\StatCont::FILE_DOWNLOAD;
        $num = D('Addons://Stat/Stat')->where($map)->getField('num');
        
        echo $num == '' ? 0 : $num;
    }
    
    public function articleViewNumRank(){
        $rank = I('get.rank');
        
        $map['type'] = \Addons\Stat\StatCont::ARTICLE_VIEW;
        $ents = D('Addons://Stat/Stat')->where($map)->order('num desc')->select();
        
        $n = 0;
        foreach($ents as $ent){
            if($n >= $rank){
                break;
            }
            $title = D('Addons://Stat/Stat')->getRefTitle($ent['id']);
            if($title === false){
                continue;
            }
            
            $data['categories'][] = $title;
            $data['series'][] = intval($ent['num']);
            $n++;
        }
        $this->ajaxReturn($data);
    }
    
    
    public function downloadRank(){
        $rank = I('get.rank');
        
        $map['type'] = \Addons\Stat\StatCont::FILE_DOWNLOAD;
        $ents = D('Addons://Stat/Stat')->where($map)->order('num desc')->select();
        $n = 0;
        foreach($ents as $ent){
            if($n >= $rank){
                break;
            }
            $title = D('Addons://Stat/Stat')->getRefTitle($ent['id']);
            if($title === false){
                continue;
            }
            
            $data['categories'][] = $title;
            $data['series'][] = intval($ent['num']);
            $n++;
        }
        $this->ajaxReturn($data);
    }
    
    public function thumbUpRank(){
        $rank = I('get.rank');
        
        $map['type'] = \Addons\Stat\StatCont::THUMBS_UP;
        $ents = D('Addons://Stat/Stat')->where($map)->order('num desc')->select();
        $n = 0;
        foreach($ents as $ent){
            if($n >= $rank){
                break;
            }
            $title = D('Addons://Stat/Stat')->getRefTitle($ent['id']);
            if($title === false){
                continue;
            }
            
            $data['categories'][] = $title;
            $data['series'][] = intval($ent['num']);
            $n++;
        }
        $this->ajaxReturn($data);
    }
    
    public function shareRank(){
        $rank = I('get.rank');
        
        $map['type'] = \Addons\Stat\StatCont::SHARE;
        $ents = D('Addons://Stat/Stat')->where($map)->order('num desc')->select();
        $n = 0;
        foreach($ents as $ent){
            if($n >= $rank){
                break;
            }
            $title = D('Addons://Stat/Stat')->getRefTitle($ent['id']);
            if($title === false){
                continue;
            }
            
            $data['categories'][] = $title;
            $data['series'][] = intval($ent['num']);
            $n++;
        }
        $this->ajaxReturn($data);
    }
    
    
}
