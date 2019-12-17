<?php

namespace Admin\Controller;

use Think\Controller;

class PublicController extends Controller {

    private $_error;
    
    public function getError(){
        return $this->_error;
    }
    
    //生成验证码图片
    public function verify($length=4,$imageW=290,$imageH=90,$fontSize=40) {

        $verify = new \Think\Verify();
        //规定验证码的长度和宽度
        $verify->length = $length;
        $verify->imageW = $imageW;
        $verify->imageH = $imageH;
        $verify->fontSize = $fontSize;
        $verify->entry();
    }

    //验证验证码是否正确
    public function checkVerify($code, $id = '') {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    //后台登录
    public function login() {
//        \Think\Log::write('login 分析');
//        \Think\Log::write(json_encode($_SERVER));
//        \Think\Log::write('login 分析');
        if(IS_POST){

            $r = $this->adminLogin(I('post.uid'), I('post.pwd'));

            if($r === false){
                $this->error($this->getError(), U('Public/login'), array('verify_show'=> isShowVerify()));
            }
            else{
                $this->success(l('login success'), U('Dashboard/index'));
            }
        }
        else{
            if (!isAdminLogin()) {

                //是否显示验证码
                if (isShowVerify()) {
                    $this->assign('verify_show', true);
                }

                $this->display();
            } else {
                $this->redirect('Dashboard/index');
            }
        }
        

    }

    //后台退出
    public function logout() {
        if (isAdminLogin()) {
            cleanRbacKey();
            cleanAuthFilterKey();

            session('ADMIN_LOGIN', null);
            sysLogs('后台登出');
        }
        $this->redirect('Public/login');
    }

    //后台登录检测
    public function adminLogin($uid, $pwd) {
        
        if (isShowVerify()) {
            //如果有验证码 则验证
            $verify = I('post.verify');
            if (!$this->checkVerify($verify)) {
                $this->loginErr(l('verify_code_error'));
                return false;
            }
        }
        
        $user_model = D('User');
        $user_ent = $user_model->getUserByEmailOrNickName($uid);
        
        
        $r = $user_model->adminLogin($uid, $user_model->hashPwd($pwd, $user_ent['salt']));

        if ($r === false) {

            $this->loginErr($user_model->getError());
            return false;
        } 
        return true;
    }
    
    //$third_login 第三方登录标记
    public function homeLogin($uid, $pwd, $third_login = false){
        //第三方登录不需验证码
        if (!$third_login && isShowVerify()) {
            //如果有验证码 则验证
            $verify = I('post.verify');
            if (!$this->checkVerify($verify)) {
                $this->loginErr(l('verify_code_error'));
                return false;
            }
        }
        
        $user_model = D('User');
        $user_ent = $user_model->getUserByEmailOrNickName($uid);
        $r = $user_model->homeLogin($uid, $user_model->hashPwd($pwd, $user_ent['salt']), $third_login);

        if ($r === false) {

            $this->loginErr($user_model->getError());
            return false;
        } 
        return true;
    }

    //登录错误处理函数
    private function loginErr($msg) {
        loginFail();
        $this->_error = $msg;
        
        //$this->error($msg, U('login'));
    }

}
