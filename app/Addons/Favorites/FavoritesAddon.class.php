<?php
namespace Addons\Favorites;
use Addons\Addon;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MessageAddon
 *
 * @author tider
 */
class FavoritesAddon extends Addon{
    //put your code here
    
    public $info = array(
        'name' => 'Favorites',
        'title' => '用户收藏夹',
        'description' => '用户可收藏自己喜欢的内容',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.1'
    );
    
    public function install(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}favorites;");
        $model->execute("CREATE TABLE {$prefix}favorites (`id` int(11) NOT NULL AUTO_INCREMENT primary key,  `uid` int(11) NOT NULL, `ref_id` int(11) NOT NULL, `ref_key` varchar(100) NOT NULL, `url` varchar(500) NOT NULL, `create_date` int(11) NOT NULL);");
        $this->createHook('userFavorites', '用户收藏夹');
        return true;
    }
    
    public function uninstall(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}favorites;");
        $this->delHook('userFavorites');
        return true;
    }
    
    public function userFavorites($uid){
        needHomeLogin();
        
        $favorites_model = D('Addons://Favorites/Favorites');
        $map['uid'] = $uid;
        $count = $favorites_model->getListForCount($map);
        $per_page = C('HOME_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        
        $favorites_ents = $favorites_model->getListForPage($map, $page->nowPage, $page->listRows, 'create_date desc');
        $favor_list = array();
        foreach($favorites_ents as $ent){
            $ref_ent = $favorites_model->getRefEnt($ent['id']);
            if(!$ref_ent){
                continue;
            }
            $ent['title'] = isset($ref_ent['title']) ? $ref_ent['title'] : '';
            $ent['cover_id'] = isset($ref_ent['cover_id']) ? $ref_ent['cover_id'] : 0;
            $favor_list[] = $ent;
        }
        $this->assign('pagination', $page->show());
        $this->assign('favor_list', $favor_list);
        $this->display(T('Addons://Favorites@default/user_favorites'));
        
    }

}
