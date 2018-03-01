<?php
namespace Addons\Favorites\Model;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FavoritesModel extends \Gy_Library\GyListModel{
    
    public function getTotalFavor(){
        $count = $this->count();
        return $count == '' ? 0 : $count;
    }
    
    public function getRefEnt($id){
        $ent = $this->where('id='. $id)->find();
        $ref_ent = D(ucfirst($ent['ref_key']))->where('id='. $ent['ref_id'])->find();
        return $ref_ent;
    }
    
    public function getRefTitle($ref_id, $ref_key){
        $ref_ent = D(ucfirst($ref_key))->where('id='. $ref_id)->find();
        if(!$ref_ent){
            return false;
        }
        return isset($ref_ent['title']) ? $ref_ent['title'] : '';
    }
    
    //文章收藏量排行
    public function rankList($count_flag, $page = 0, $per_page = 0){
        $sql = "select count(*) num, ref_id, ref_key from __FAVORITES__ group by ref_id, ref_key order by num desc";
        
        if($count_flag === true){
            $data_list = M()->query($sql);
            return count($data_list);
        }
        else{
            $sql .= ' limit ' . ($page - 1) * $per_page . ',' . $per_page;
            $data_list = M()->query($sql);
            return $data_list;
        }
    }
}

