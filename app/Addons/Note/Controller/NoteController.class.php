<?php
namespace Addons\Note\Controller;
use Home\Controller\AddonsController;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NoteController
 *
 * @author tider
 */
class NoteController extends AddonsController{
    //put your code here
    
    public function read(){
        needHomeLogin();
        
        $id=I('id');
        $NoteModel = D('Addons://Note/Note');
        $row=$NoteModel->getUserNote($id, session(C('USER_AUTH_KEY')));
        $NoteModel->where(array('id'=>$id, 'to_uid' => session(C('USER_AUTH_KEY'))))->setField('read',1);
        $data = $row['content'];
        $ajax=array(
            'status'=>1,
            'data'=>$data,
        );
        $this->ajaxReturn($ajax);
    }
    
    public function delete(){
        needHomeLogin();
        
        $id = I('id');
        $note_model = D('Addons://Note/Note');
        $note_ent = $note_model->getOne($id);
        if($note_ent['to_uid'] != session(C('USER_AUTH_KEY'))){
            E('您删除的不是自己的消息');
        }
        
        $r = $note_model->where(array('id'=>$id, 'to_uid' => session(C('USER_AUTH_KEY'))))->delete();
        if($r){
            $this->success('删除成功');
        }else{
            $this->error($note_model->getError());
        }
    }
    
    public function sendNote(){
        needHomeLogin();
        
        $note_addon = new \Addons\Note\NoteAddon();
        $config = $note_addon->getConfig();
        
        if($config['sendnote'] == 2){
            E('消息发送功能未启用');
        }
        
        $nick_name = I('nick_name');
        $content = I('content');
        $time=NOW_TIME;
        if(is_array($nick_name)){
            $size = count($nick_name);
            if($size>5){
                $this->error('一次性最多可以给5个用户发短消息!');
            }
            $uid = session(C('USER_AUTH_KEY'));
            $data = array();
            foreach($nick_name as $k=>$v){
                $data[$k]['from_uid'] = $uid;
                $data[$k]['to_uid'] = $v;
                $data[$k]['content']= $content;
                $data[$k]['type'] = \Addons\Note\NoteCont::NOTE_TYPE_MSG;
                $data[$k]['create_date'] = $time;

                if($uid==$v){
                    $this->error('不能给自己发消息');
                }
            }

            $NoteModel = D('Note');
            $r=$NoteModel->addAll($data);
            if($r){
                $this->success('发送成功',U('User/userMessage'));
            }else{
                $this->error($NoteModel->getError());
            }

        }else{
            $this->error('请输入昵称!');
        }
    }
}
