<?php
/**
 *@第三方帐号集成  注册绑定 模块
 *@author: jry
 *@date:   2014-08-15
 * */
 
namespace Addons\SyncLogin\Controller;
use Home\Controller\AddonsController; 
use User\Api\UserApi as UserApi;

class RegisterController extends AddonsController{
     
    /**
     * 第三方帐号集成 - 绑定本地帐号
     * @return void
     */
    public function dobind(){
        parent::autoCheckToken();
        $uid = I('post.uid');
        $pwd = I('post.pwd');
        
        $user_info = session('third_login_user');
        $token = session('third_login_token');
        
        if(!$user_info || !$token){
            E('查找不到user_info或token的session信息');
        }
        
        //根据邮箱地址和密码判断是否存在该用户
        $public_controller = new \Admin\Controller\PublicController();
        $r = $public_controller->homeLogin($uid, $pwd);
        
        if($r === true) {
            //注册来源-第三方帐号绑定
            
            if( isset($_POST)){
                $user_ent = D('User')->getUserByEmailOrNickName($uid);
                
                $sync_login_model = D('Addons://SyncLogin/SyncLogin');
                $r = $sync_login_model->bindUser($user_ent['id'], sf($token['openid']), sf($user_info['type']), sf($token['access_token']), sf($token['refresh_token']));
                if($r === false){
                    $this->error($sync_login_model->getError());
                }
            }else{
                $this->error('绑定失败，第三方信息不正确');
            }      
            $this->assign('jumpUrl', U('Home/Index/index'));
            $this->success('恭喜您，绑定成功');
        }else{
            $this->error($public_controller->getError());            // 注册失败
        } 
        
    }
    

     /**
     * 第三方帐号集成 - 取消绑定本地帐号
     * @return void
     */
    public function cancelbind(){
        $condition['uid'] = I('get.uid');
        $condition['type'] = I('get.type');
        $ret = D('sync_login')->where($condition)->delete();
        //echo $ret;exit;
        if($ret){
            $this->success('取消绑定成功');
        }else{
            $this->error('取消绑定失败');
        }
    }
    
    
    /**
     * 第三方帐号集成 - 注册新账号
     * @return void
     */
    public function doregister(){
        parent::autoCheckToken();
        if(I('post.agree') != 'on'){
            $this->error('同意服务条款才能注册', U('home/addons/execute', array('_addons'=>'SyncLogin', '_controller'=>'Base','_action'=>'callback', 'type'=>'qq')));
        }
        
        $email = I('post.email');
        $username = I('post.nick_name');
        $password = I('post.pwd');
        $repassword = I('post.repwd');

        /* 检测密码 */
        if($password != $repassword){
            $this->error('密码和重复密码不一致！',U('home/addons/execute', array('_addons'=>'SyncLogin', '_controller'=>'Base','_action'=>'callback', 'type'=>'qq')));
        }
        
        
        $user_model = D('User');
        $data['email'] = $email;
        $data['nick_name'] = $username;
        $data['pwd'] = $password;
        $data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        
        $user_info = session('third_login_user');
        $token = session('third_login_token');
        
        if(!$user_info || !$token){
            E('查找不到user_info或token的session信息');
        }
        
        if(!empty($user_info['head'])){
            $file_pic_id = D('FilePic')->add(array('url' => $user_info['head'], 'upload_date' => time()));
            if($file_pic_id !== false){
                $data['portrait'] = $file_pic_id;
            }
        }
        
        $user_profile_data['real_name'] = I('post.real_name');
        $user_profile_data['gender'] = I('post.gender');
        $user_profile_data['company'] = I('post.company');
        $user_profile_data['join_activity'] = I('post.join_activity');
        $user_profile_data['reason'] = I('post.reason');
        
        $uid   =   $user_model->register($data, $user_profile_data);
        if(0 < $uid){
            // 注册来源-第三方帐号绑定
            
            $sync_login_model = D('Addons://SyncLogin/SyncLogin');
            
            $r = $sync_login_model->bindUser($uid, sf($token['openid']), sf($user_info['type']), sf($token['access_token']), sf($token['refresh_token']));
            if($r === false){
                $this->error($sync_login_model->getError(), U('/home/user/bindSocial'));
            }
            else{
                $this->success('注册已提交，请等待管理员审批', U('index/index'));
            }
        }else{
            $this->error($user_model->getError(), U('home/addons/execute', array('_addons'=>'SyncLogin', '_controller'=>'Base','_action'=>'callback', 'type'=>$user_info['type']))); //注册失败
        }
    }
} 

