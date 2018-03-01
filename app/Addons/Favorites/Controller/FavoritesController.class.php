<?php
namespace Addons\Favorites\Controller;
use Home\Controller\AddonsController;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FavoritesController extends AddonsController{
    
    public function add(){
        needHomeLogin();
        
        $ref_id = I('get.ref_id');
        $ref_key = I('get.ref_key');
        $url = I('get.url');
        
        $uid = session(C('USER_AUTH_KEY'));
        
        $map['uid'] = $uid;
        $map['ref_id'] = $ref_id;
        $map['ref_key'] = $ref_key;
        $favor_ent = D('Addons://Favorites/Favorites')->where($map)->find();
        
        if($favor_ent){
            D('Addons://Favorites/Favorites')->where('id=' . $favor_ent['id'])->delete();
            
            $ajax_data['status'] = 1;
            $ajax_data['info'] = '-1';
            $this->ajaxReturn($ajax_data);
        }
        else{
            $favorites_addon = new \Addons\Favorites\FavoritesAddon();
            $config = $favorites_addon->getConfig();
            if(!in_array($ref_key, parse_config_attr($config['ref_keys']))){
                $ajax_data['status'] = 0;
                $ajax_data['info'] = '收藏功能暂时不支持该类型内容';
                $this->ajaxReturn($ajax_data);
            }
            
            $data['uid'] = $uid;
            $data['ref_id'] = $ref_id;
            $data['ref_key'] = $ref_key;
            $data['url'] = $url;
            $data['create_date'] = time();

            D('Addons://Favorites/Favorites')->add($data);
            
            $ajax_data['status'] = 1;
            $ajax_data['info'] = '+1';
            $this->ajaxReturn($ajax_data);
        }
        
    }
    
    public function getFavorNum(){
        $ref_id = I('get.ref_id');
        $ref_key = I('get.ref_key');
        
        $map['ref_id'] = $ref_id;
        $map['ref_key'] = $ref_key;
        $count = D('Addons://Favorites/Favorites')->where($map)->count();
        echo $count == '' ? 0 : $count; 
    }
    
    public function checkFavor(){
        needHomeLogin();
        
        $uid = session(C('USER_AUTH_KEY'));
        $ref_id = I('get.ref_id');
        $ref_key = I('get.ref_key');
        
        $map['uid'] = $uid;
        $map['ref_id'] = $ref_id;
        $map['ref_key'] = $ref_key;
        
        $favor_ent = D('Addons://Favorites/Favorites')->where($map)->find();
        if($favor_ent){
            $ajax_data['status'] = 1;
            $this->ajaxReturn($ajax_data);
        }
        else{
            $ajax_data['status'] = 0;
            $this->ajaxReturn($ajax_data);
        }
    }
    
    public function favorRank(){
        $rank = I('get.rank');
        
        $sql = "SELECT count(*) count, ref_id, ref_key FROM __FAVORITES__ group by ref_id, ref_key order by count desc";
        
        $ents = M()->query($sql);
        $n = 0;
        foreach($ents as $ent){
            if($n >= $rank){
                break;
            }
            
            $title = D('Addons://Favorites/Favorites')->getRefTitle($ent['ref_id'], $ent['ref_key']);
            if($title === false){
                continue;
            }
            
            $data['categories'][] = $title;
            $data['series'][] = intval($ent['count']);
            $n++;
        }
        $this->ajaxReturn($data);
    }
}

