<?php

namespace Common\Model;

use \Gy_Library\DBCont;

class UserModel extends \Gy_Library\GyListModel implements \Gy_Library\ICheckAvailable {

    protected $_validate = array(
        array('status', 'require', '必选填写用户状态'),
        array('status', array(DBCont::FORBIDDEN_STATUS, DBCont::NORMAL_STATUS), '{%STATUS_OUT_OF_RANGE}', parent::MUST_VALIDATE, in, parent::MODEL_BOTH),
        array('pwd', 'require', '{%PWD_REQUIRE}'),
        array('telephone', 'require', '手机号码必填'),
        array('telephone','/^1\d{10}$/','手机号码格式不正确',parent::MUST_VALIDATE,'regex'),
        array('email', 'require', '电子邮箱必填'),
        array('email', 'email', '{%EMAIL_FORMAT_ERROR}'),
    );
    protected $_auto = array(
        array('register_date', "time", parent::MODEL_INSERT, 'function'),
    );
    protected $_delete_validate = array(
        array(array('1'), 'id', parent::NOT_ALLOW_VALUE_VALIDATE, '不能删除超级管理员账户'),
        array('Syslogs', 'userid', parent::EXIST_VALIDATE, '已经产生了系统日记，该用户只能禁用，不能删除'),
    );
    protected $_delete_auto = array( 
        array('delete',  'RoleUser', array('id' => 'user_id')),
    );
    protected $_forbid_validate = array(
        array(array('1'), 'id', parent::NOT_ALLOW_VALUE_VALIDATE, '不能禁用超级管理员账户'),
    );

    
    public function checkAvailable($id) {
        $map['status'] = DBCont::NORMAL_STATUS;
        $map['id'] = $id;
        $ent = $this->where($map)->find();
        if($ent){
            return true;
        }
        else{
            return false;
        }
    }

    public function idUniqueCallBack($id) {
        return !$this->isExistsUser($id);
    }

    public function getUserRole($id) {
        return $this->join('__ROLE_USER__ ON __USER__.id = __ROLE_USER__.user_id')
                        ->join('__ROLE__ ON __ROLE_USER__.role_id = __ROLE__.id')
                        ->where(array('qs_user.id' => $id))
                        ->field('qs_role.*')->select();
    }
    
    //获取用户的称呼，如昵称为空，则以邮件代替
    public function getUserName($id){
        $user_ent = $this->where(array('id' => $id))->find();
        if(!$user_ent){
            return null;
        }
        else{
            return $user_ent['nick_name']? $user_ent['nick_name']: $user_ent['telephone'];
        }
    }

    public function isExistsUser($user_id) {
        $user = $this->getById($user_id);
        if (!$user) {
            return false;
        } else {
            return true;
        }
    }
    
    public function searchByEmail($word){
        $map['email'] = array('like', '%' . $word . '%');
        
        return $this->where($map)->field('email as id, email as text')->select();
    }

    
    public function getUserByEmail($email) {
        $map['email'] = $email;
        return $this->getUser($map);
    }

    public function getUserByTel($tel) {
        $map['telephone'] = $tel;
        return $this->getUser($map);
    }

    public function getUser($map) {
        return $this->where($map)->find();
    }
    
    
    public function getUserByEmailOrNickName($email_or_nickName){
        if(filter_var($email_or_nickName, FILTER_VALIDATE_EMAIL) === false){
            $map['nick_name'] = $email_or_nickName;
        }
        else{
            $map['email'] = $email_or_nickName;
        }
        
        return $this->getUser($map);
                
    }

    public function getUserList($map) {
        return $this->where($map)->select();
    }
    
    //前台登录
    //$third_login 为 true时是第三方登录，不需进行密码验证
    public function homeLogin($login_name, $pwd_hash, $type='person', $third_login = false){
        
        switch($type){
            case 'person':
                $data = $this->where(array('telephone' => $login_name, 'user_type' => 'person', 'status' => DBCont::NORMAL_STATUS))->find();
                break;
            case 'company':
                $data = $this->where(array('nick_name' => $login_name, 'user_type' => 'company', 'status' => DBCont::NORMAL_STATUS))->find();
                break;
            default:
                $data = null;
                break;
        }
        

        if(!$data || ($data['user_type'] != 'person' && $data['user_type'] != 'company')){
            $this->error = '用户不存在';
            return false;
        }
        
        $r = $this->_login($data['id'], $pwd_hash, $third_login);
         if($r === false){
            return false;
         }
         else{

            \Think\Hook::listen('after_home_login', $data['id']);
            session('HOME_LOGIN', true);
            session('ADMIN_LOGIN', null);
            return true;
         }
    }
    
    public function getUserIndexUrl($uid){
        $type = $this->where('id='. $uid)->getField('user_type');
        switch ($type){
            case 'person':
                return U('/home/user/index');
            case 'company':
                return U('/home/company/index');
            default:
                E('用户类型异常');
                break;
        }
    }
    
    
    //进一步分解_login，已满足一键登录功能
    private function _login($id, $pwd_hash,$third_login = false){
        if($third_login === false){
            if ($pwd_hash == '') {
                $this->error = '请输入密码';
                return false;
            }
        }
        
        $data = $this->getOne($id);
        
        if($third_login === false){
            if (!$data || $data['pwd'] != $pwd_hash) {
                 $this->error = l('username_or_password_error');
                 return false;
             }
        }
        
        if (count($this->data) == 0) {
            E('model \'s data property is null!');
        }

        //非正常状态禁止登录
        if($this->data['status'] != DBCont::NORMAL_STATUS){
            $this->error = '用户被禁用';
            return false;
        }
        
        $this->data['last_login_time'] = time();
        $this->data['last_login_ip'] = get_client_ip();
        
        $uid = $this->data['id'];
        $this->startTrans();
        
        try{

            $r = $this->save();

            $this->commit();
        }
        catch(\Think\Exception $ex){
            $this->rollback();
            E($ex->getMessage());
        }
        if ($r !== false) {

            if (!C('USER_AUTH_ADMINID')) {
                E('C("USER_AUTH_ADMINID") is null');
            }
            
            session(C('USER_AUTH_KEY'), $uid);

            //设置超级管理员权限
            if ($uid == C('USER_AUTH_ADMINID')) {
                
                session(C('ADMIN_AUTH_KEY'), true);
            } else {
                session(C('ADMIN_AUTH_KEY'), false);
            }
            
            return true;
        } else {
            E('user login save error!');
        }
    }
    

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
    
    public function genActiveToken($email, $pwd_md5){
        return md5($email . $pwd_md5 . time());
    }
    
    
    public function modifyEmail($token){
        $map['token'] = $token;
        $map['status'] = DBCont::NORMAL_STATUS;
        $user_ent = $this->where($map)->find();
        
        $old_email = $user_ent['email'];
        
        if(!$user_ent){
            $this->error = '无效token';
            return false;
        }
        
        if($user_ent['token_exptime'] > time()){
            $json = decrypt($token, C('ENCRYPT_KEY'));
            
            $user_ent['token'] = '';
            $user_ent['token_exptime'] = '';
            
            $arr = json_decode($json, true);
            
            $user_ent['email'] = $arr['email'];
            
            //跳转页面，无令牌验证，关闭令牌验证
            C('TOKEN_ON', false);
            $r = $this->createSave($user_ent);
            if($r=== false){
                return false;
            }
            else{
                sysLogs('修改邮箱:' . $old_email . ' => ' . $arr['email']);
                
                return true;
            }
        }
        else{
            $this->error = 'token已过期';
            return false;
        }
    }
    
    public function bindUser($data){
        if($data['email'] == '' || $data['telephone'] == ''){
            $this->error = 'email和手机不能为空';
            return false;
        }

        $user_ent = $this->getUserByEmail($data['email']);

        if(!$user_ent){
            //不存在账号，新建
            $data['pwd'] = generateInitPwd();
            $data['status'] = DBCont::NORMAL_STATUS;
            $data['token'] = md5($data['email'] . $data['pwd'] . time());
            $data['token_exptime'] = genTokenExptime();

            $r = $this->createAdd($data);
            if($r === false){
                return false;
            }
            $data['id'] = $r;
            $data['_type'] ='add';
            return $data;
                
        }else{
            //存在账号 更新

            //由于有些form带有status字段，会将用户的status状态改掉导致bug
            //同时应该秉持尽量减少函数职能的原则，故获取用户Id并更新用户资料的功能去除
            //$user_ent = array_merge($user_ent, $data);
            
            $user_ent['_type'] = 'edit';
            return $user_ent;
        }
        
    }
    
    public function resetPwd($data){
        $pwd = $data['pwd'];
        if($this->_validationPwd($pwd) === false){
            return false;
        }
        
        $code = $data['mobile_code'];
        $expired = session($data['telephone'] . '_mobile_validate_expired');
        if(time() > $expired){
            $this->error = '手机验证码错误';
            return false;
        }
        
        if(!$code || $code != session($data['telephone'] . '_mobile_validate_code')){
            $this->error = '手机验证码错误';
            return false;
        }
        
        $type = $data['type'];
        switch($type){
            case 'p':
                $map['telephone'] = $data['telephone'];
                $map['user_type'] = 'person';
                $user_ent = $this->where($map)->find();
                break;
            case 'c':
                $map['telephone'] = $data['telephone'];
                $map['user_type'] = 'company';
                $user_ent = $this->where($map)->find();
                break;
            default:
                E('类型错误');
                break;
        }
        
        if(!$user_ent){
            $this->error = '用户不存在';
            return false;
        }
        
        $user_ent['salt'] = $this->_genSalt();
        $user_ent['pwd'] = $this->hashPwd($pwd, $user_ent['salt']);
        
        if($this->createSave($user_ent) === false){
            return false;
        }
        
        return true;
    }
    
    public function modifyPwdByUser($o_pwd, $pwd){
        if($this->_validationPwd($pwd) === false){
            return false;
        }
        
        $uid = session(C('USER_AUTH_KEY'));
        
        $ent = $this->getOne($uid);
        
        if($ent['pwd'] != $this->hashPwd($o_pwd, $ent['salt'])){
            $this->error = '原密码不正确';
            return false;
        }
        
        $ent['salt'] = $this->_genSalt();
        $ent['pwd'] = $this->hashPwd($pwd, $ent['salt']);
        
        if($this->createSave($ent) === false){
            return false;
        }
        
        return true;
    }
    
    public function modifyPwdByAdmin($uid, $pwd){
         if($this->_validationPwd($pwd) === false){
            return false;
        }
        
        $ent = $this->getOne($uid);
        
        
        
        $ent['salt'] = $this->_genSalt();
        $ent['pwd'] = $this->hashPwd($pwd, $ent['salt']);
        
        if($this->createSave($ent) === false){
            return false;
        }
        
        return true;
    }
    
    //新增用户统一函数
    public function newUser($data){
          
        if($this->_validationPwd($data['pwd']) === false){
            return false;
        }
        
        //随机生成6位salt
        $data['salt'] = $this->_genSalt();
        $data['pwd'] = $this->hashPwd($data['pwd'], $data['salt']);
        
        return $this->createAdd($data);
    }
    
    
    
    //验证密码规则统一函数
    private function _validationPwd($pwd){
        //密码长度必须在6-12位之间
        if(strlen($pwd)>=6 && strlen($pwd)<=12){
            return true;
        }
        else{
            $this->error = '密码长度必须在6-12位';
            return false;
        }
    }
    
    //统一用户加密散列函数
    public function hashPwd($ori_pwd, $salt){
        return md5(md5($ori_pwd) . $salt);
    }
    
    private function _genSalt(){
        return mt_rand(100000, 999999);
    }
    
    public function getUserByNickNames($nick_names){
        
        $map['status'] = DBCont::NORMAL_STATUS;
        $map['nick_name'] = array('in', $nick_names);
        return $this->where($map)->select();
        
    }


    public function select2Search($nick_name){
        $map['nick_name'] = array('like' , '%' . $nick_name . '%');
        $map['status'] = DBCont::NORMAL_STATUS;
        return $this->where($map)->field('id as id, nick_name as text')->select();
    }
    
    public function totalNormalUser(){
        $map['status'] = DBCont::NORMAL_STATUS;
        return $this->where($map)->count();
    }
    
    public function modifyPwd($uid, $pwd){
        if($this->_validationPwd($pwd) === false){
            return false;
        }
        
        $user_ent = $this->getOne($uid);
        
        $user_ent['salt'] = $this->_genSalt();
        $user_ent['pwd'] = $this->hashPwd($pwd, $user_ent['salt']);
        
        return $this->createSave($user_ent);
    }
    
    public function newCompanyUser($data){
        $data['user_type'] = 'company';
        
        if($this->checkTelephoneForCompanyUser($data['telephone']) === false){
            $this->error = '手机已被注册';
            return false;
        }
        
        if(!$data['nick_name']){
            $this->error = '用户名必填';
            return false;
        }
        
        $ent = $this->where(array('nick_name' => $data['nick_name'], 'user_type' => 'company'))->find();
        if($ent){
            $this->error = '机构已被注册';
            return false;
        }
        
        if(!$data['name']){
            $this->error = '机构名称必填';
            return false;
        }
        
        if(!$data['contact']){
            $this->error = '联系人必填';
            return false;
        }
        
        $user_id = $this->newUser($data);
        if($user_id === false){
            return false;
        }
        
        $data['uid'] = $user_id;
        $company_profile_model = D('CompanyProfile');
        if($company_profile_model->createAdd($data, D('User'), $user_id) === false){
            $this->error = $company_profile_model->getError();
            return false;
        }
        
        //关联图片的宿主账号
        if($data['image']){
            $images = explode(',', $data['image']);
            foreach($images as $v){
                D('FilePic')->bindUser($v, $user_id);
            }
        }
        
        return $user_id;
        
    }
    
    public function saveCompanyUser($data){
        $user_id = $data['id'];
        $user_ent = $this->getOne($user_id);
        $old_user_ent = $user_ent;
        if(!$user_ent){
            $this->error = '用户不存在';
            return false;
        }
        
        unset($data['id']);
        unset($data['salt']);
        unset($data['pwd']);
        unset($data['register_date']);
        unset($data['user_type']);
        unset($data['last_login_time']);
        unset($data['last_login_ip']);
        $user_ent = array_merge($user_ent, $data);
        if($this->createSave($user_ent) === false){
            return false;
        }
        else{
            $company_model = D('CompanyProfile');
            $compay_ent = $company_model->where(array('uid' => $user_id))->find();
            unset($data['uid']);
            $compay_ent = array_merge($compay_ent, $data);
            if($company_model->createSave($compay_ent, $this, $old_user_ent) === false){
                $this->error = $company_model->getError();
                return false;
            }
        }
        return true;
    }
    
    public function newCommonUser($data){
        $data['user_type'] = 'person';

        if($this->checkTelephoneForCommonUser($data['telephone']) === false){
            $this->error = '手机已被注册';
            return false;
        }

        $user_id = $this->newUser($data);
        if($user_id === false){
            return false;
        }
        
        $data['uid'] = $user_id;
        $person_profile_model = D('PersonProfile');
        if($person_profile_model->createAdd($data, D('User'), $user_id) === false){
            $this->error = $person_profile_model->getError();
            return false;
        }
        return $user_id;
    }
    
    public function saveCommonUser($data){
        $user_id = $data['id'];
        $user_ent = $this->getOne($user_id);
        $old_user_ent = $user_ent;
        if(!$user_ent){
            $this->error = '用户不存在';
            return false;
        }

        //需要更新的fields
        isset($data['email']) && $user_ent['email'] = $data['email'];
        isset($data['portrait']) && $user_ent['portrait'] = $data['portrait'];
        isset($data['nick_name']) && $user_ent['nick_name'] = $data['nick_name'];
        
        if(isset($data['telephone'])){
            $user_ent['telephone'] = $data['telephone'];
            $where['id'] = array('neq', $user_id);
            $where['telephone'] = $data['telephone'];
            $where['user_type'] = 'person';
            $ent = $this->where($where)->find();
            if($ent){
                $this->error = '手机号码已被注册';
                return false;
            }
        }

        if($this->createSave($user_ent) === false){
            
            return false;
        }
        else{ 
            $profile_model = D('PersonProfile');
            $profile_ent = $profile_model->where(array('uid' => $user_id))->find();
            unset($data['id']);
            unset($data['uid']);
            $profile_ent = array_merge($profile_ent, $data);
            if($profile_model->createSave($profile_ent, $this, $old_user_ent) === false){
                $this->error = $profile_model->getError();
                return false;
            }       

        }
        
        return true;
    }
    
    public function checkTelephoneForCommonUser($tel){
        $map['user_type'] = 'person';
        $map['telephone'] = $tel;
        $ent = $this->where($map)->find();
        return $ent ? false : true;
    }
    
    public function checkTelephoneForCompanyUser($tel){
        $map['user_type'] = 'company';
        $map['telephone'] = $tel;
        $ent = $this->where($map)->find();
        return $ent ? false : true;
    }
    
     public function sendValidateSms($mobile){
        $limit_model = D('SmsLimit');
        $r = $limit_model->checkMobile($mobile);
        if($r === false){
            $this->error = $limit_model->getError();
            return false;
        }
        
        $code = mt_rand(1000, 9999);
        
        $para['content'] = '验证码:' . $code;
        $para['mobile'] = $mobile;
        \Think\Hook::listen('sendSmsOnce', $para);
        
        $return = $para['return'];
        
        if($return['status'] == 1){
            session($mobile . '_mobile_validate_code', $code);
            session($mobile . '_mobile_validate_expired', time()+180);
            return true;
        }
        else{
            $this->error = $return['err_msg'];
            return false;
        }
    }
    
    public function register($data){
        
        $code = $data['mobile_code'];
        $expired = session($data['telephone'] . '_mobile_validate_expired');
        if(time() > $expired){
            $this->error = '手机验证码错误';
            return false;
        }
        
        if(!$code || $code != session($data['telephone'] . '_mobile_validate_code')){
            $this->error = '手机验证码错误';
            return false;
        }
        
        $type = $data['type'];
        
        switch($type){
            case 'person':
                $data['status'] = DBCont::NORMAL_STATUS;
                $r = $this->newCommonUser($data);
                if($r !== false){
                    $ent = $this->getOne($r);
                    $this->homeLogin($ent['telephone'], $ent['pwd']);
                }
                break;
            case 'company':
                $data['status'] = DBCont::NORMAL_STATUS;
                $r = $this->newCompanyUser($data);
                 if($r !== false){
                    $ent = $this->getOne($r);
                    $this->homeLogin($ent['nick_name'], $ent['pwd'], 'company');
                }
                break;
        }
         
        return $r;
    }
    
    public function getPersonInfo($uid){
        $user_ent = $this->getOne($uid);
        $profile_ent = D('PersonProfile')->getByUid($uid);
        
        unset($profile_ent['id']);
        unset($profile_ent['uid']);
        $user_info = array_merge($user_ent, $profile_ent);
        return $user_info;
    }
    
    public function getCompanyInfo($uid){
        $user_ent = $this->getOne($uid);
        $company_ent = D('CompanyProfile')->getByUid($uid);
        unset($company_ent['id']);
        unset($company_ent['uid']);
        $company_info = array_merge($user_ent, $company_ent);
        return $company_info;
    }
    
    public function fillUser($uid, $data){
        $user_ent = $this->getOne($uid);
        
        if(!$user_ent){
            $this->error = '用户不存在';
            return false;
        }
        
        if(!$user_ent['portrait']){
            $user_ent['portrait'] = $data['portrait'];
        }
        
        if(!$user_ent['nick_name']){
            $user_ent['nick_name'] = $data['nick_name'];
        }
        
        return $this->createSave($user_ent);
    }
    
    public function getCompanyListForExcel($map){
        $excel_header = array(
            '登陆用户名',
            '机构名称',
            '负责人',
            '负责人联系电话',
            '负责人邮箱',
            '机构网址',
            '地址'
        );
        
        $excel_list[] = $excel_header;
        
        $list = $this->getList($map);
        foreach($list as $v){
            $profile_ent = D('CompanyProfile')->getByUid($v['id']);
            $excel = array();
            
            $excel[] = $v['nick_name'];
            $excel[] = $profile_ent['name'];
            $excel[] = $profile_ent['contact'];
            $excel[] = $v['telephone'];
            $excel[] = $v['email'];
            $excel[] = $profile_ent['site'];
            $excel[] = $profile_ent['address'];
            
            $excel_list[] = $excel;
        }
        return $excel_list;
    }

}
