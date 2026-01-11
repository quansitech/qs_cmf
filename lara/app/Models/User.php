<?php
namespace app\Models;

use \Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;

class User extends Model{

    protected $table = 'qs_user';

    public $timestamps = false;
    public $error = "";

 
     //后台登录
    public function adminLogin($login_name, $pwd_hash) {
        //当用户上传大文件时，时间会非常长，为了方便其有充足的上传时间，而设置了一天的过期时间
        $data = $this->getUserByEmailOrNickName($login_name);
        if(!$data){
            $this->error = '用户不存在';
            return false;
        }
        $r = $this->_login($data['id'], $pwd_hash);
         if($r === false){
             return false;
         }
         session('ADMIN_LOGIN', true);
         session('HOME_LOGIN', null);
         sysLogs('后台登录');
         return true;
    }

    public function getUserByEmailOrNickName($email_or_nickName){
        if(filter_var($email_or_nickName, FILTER_VALIDATE_EMAIL) === false){
            $map['nick_name'] = $email_or_nickName;
        }
        else{
            $map['email'] = $email_or_nickName;
        }

        $user_ent = $this->where($map)->first();
        
        return $user_ent ? $user_ent->toArray() : null;
                
    }

    //进一步分解_login，已满足一键登录功能
    private function _login($id, $pwd_hash){
        $user = $this->where('id', $id)->first();
        
        if (!$user) {
            E('model \'s data property is null!');
        }

        //非正常状态禁止登录
        if($user->status != DBCont::NORMAL_STATUS){
            $this->error = '用户被禁用';
            return false;
        }
        
        $user->last_login_time = time();
        $user->last_login_ip = get_client_ip();
        
        $user->save();
        
        cleanRbacKey();

        if (!C('USER_AUTH_ADMINID')) {
            E('C("USER_AUTH_ADMINID") is null');
        }
            
        session(C('USER_AUTH_KEY'), $user->id);

        //设置超级管理员权限
        if ($user->id == C('USER_AUTH_ADMINID')) {
            
            session(C('ADMIN_AUTH_KEY'), true);
        } else {
            session(C('ADMIN_AUTH_KEY'), false);
        }
            
        return true;
    }

    public function hashPwd($ori_pwd, $salt){
        return md5(md5($ori_pwd) . $salt);
    }
}