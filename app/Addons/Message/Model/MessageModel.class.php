<?php
namespace Addons\Message\Model;
use \Gy_Library\DBCont;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MessageModel
 *
 * @author tider
 */
class MessageModel extends \Gy_Library\GyListModel{
    
    protected $_validate = array(
        array('rely_id', 'require', 'rely_id必填'),
        array('rely_key', 'require', 'rely_key必填'),
        array('uid', 'require', '找不到评论人信息')
    );
    
    public function getLastRelyTime($uid){
        $map['uid'] = $uid;
        return $this->where($map)->max('create_date');
    }
    
    public function getMessageListForPage($rely_id, $rely_key, $page, &$count, $flag_browse = false, $message_id = 0){
        $per_page = C('HOME_PER_PAGE_NUM');
        $order = 'create_date desc';
        
        $map['rely_id'] = $rely_id;
        $map['rely_key'] = $rely_key;
        
        if($flag_browse === false){
            $message_list = $this->getListForPage($map, $page, $per_page, $order);
            $count = $this->getListForCount($map);

            $start_floor = $count - $per_page*($page - 1);
            foreach($message_list as $key => $val){
                $val['nick_name'] = $this->getNickName($val['id']);
                $val['portrait'] = $this->getPortrait($val['id']);
                $val['floor'] = $start_floor--;
                if($val['rely_message']){
                    $rely_ent = $this->getOne($val['rely_message']);
                    $val['rely_m_nick_name'] = $this->getNickName($val['rely_message']);
                    $val['rely_m_content'] = $this->getContent($val['rely_message']);
                    $val['rely_m_status'] = $rely_ent['status'];
                }
                $message_list[$key] = $val;
            }
            return $message_list;
        }
        else{
            $sql = "select page from (select @row:=@row+1 row, floor((@row-1)/@per_page)+1 page, id from (select @row:=0, @per_page:={$per_page}) r, __MESSAGE__ "
            . "where rely_id={$map['rely_id']} and rely_key='{$map['rely_key']}' order by {$order}) s where id={$message_id}";
            return $this->query($sql);
        }
    }
    
    public function getNickName($message_id){
        $m_ent = $this->getMessage($message_id);
        if(!$m_ent){
            return false;
        }
        
        $u_ent = D('User')->getOne($m_ent['uid']);
        return $m_ent['anon'] == 1 ? '匿名用户' : $u_ent['nick_name'];
    }
    
    public function getPortrait($message_id){
        $m_ent = $this->getMessage($message_id);
        if(!$m_ent){
            return false;
        }
        
        $u_ent = D('User')->getOne($m_ent['uid']);
        return $m_ent['anon'] == 1 ? 0 : $u_ent['portrait'];
    }
    
    public function getMessage($message_id){
        $map['id'] = $message_id;
        
        return $this->where($map)->find();
    }
    
    public function getContent($message_id){
        $m_ent = $this->getMessage($message_id);
        if(!$m_ent){
            return false;
        }
        
        return $m_ent['content'];
    }
    
    public function getContentForList($id){
        $ent = $this->getOne($id);
        if(!$ent){
            return false;
        }
        
        if($ent['rely_message']>0){
            $rely_ent = $this->getOne($ent['rely_message']);
            if($rely_ent){
                $user_ent = D('User')->getOne($rely_ent['uid']);
                return '回复 ' . $user_ent['nick_name'] . ':' . $ent['content'];
            }
            else{
                return $ent['content'];
            }
        }
        else{
            return $ent['content'];
        }
    }
    
    public function getRelyCate($id){
        $ent = $this->where('id='. $id)->find();
        $rely_ent = D(ucfirst($ent['rely_key']))->where('id='. $ent['rely_id'])->find();
        if(!$rely_ent){
            return '已删除';
        }
        $cate_ent = D(ucfirst($ent['rely_key']) . 'Cate')->where('id=' . $rely_ent['cate_id'])->find();
        if(!$cate_ent){
            return '已删除';
        }
        return isset($cate_ent['name']) ? $cate_ent['name'] : '';
    }
    
    public function getRelyTitle($id){
        $ent = $this->where('id='. $id)->find();
        $rely_ent = D(ucfirst($ent['rely_key']))->where('id='. $ent['rely_id'])->find();
        if(!$rely_ent){
            return '已删除';
        }
        return isset($rely_ent['title']) ? $rely_ent['title'] : '';
    }
    
    public function getMessageNum($rely_id, $rely_key){
        
        if($rely_id == '' || $rely_key == ''){
            exit('参数不全');
        }
        
        $sql = "SELECT count(*) count FROM __MESSAGE__ where rely_id={$rely_id} and rely_key='{$rely_key}' group by rely_id, rely_key";
        
        $ents = M()->query($sql);
        return $ents ? $ents[0]['count'] : 0;
    }
    
}
