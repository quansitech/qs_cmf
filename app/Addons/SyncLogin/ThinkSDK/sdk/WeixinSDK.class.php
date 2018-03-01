<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
// | QqSDK.class.php 2013-02-25
// +----------------------------------------------------------------------

class WeixinSDK extends ThinkOauth{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    
    protected $GetRequestCodeURL = '';
    
    //开放平台微信登录接口
    protected $GetRequestCodeURLW = 'https://open.weixin.qq.com/connect/qrconnect';
    
    //公众平台网页授权接口
    protected $GetRequestCodeURLM = 'https://open.weixin.qq.com/connect/oauth2/authorize';


    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $GetAccessTokenURL = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    
    /**
     * 获取request_code的额外参数,可在配置中修改 URL查询字符串格式
     * @var srting
     */
    // protected $Authorize = 'scope=get_user_info,add_share';
    protected $Authorize = 'scope=snsapi_login,snsapi_userinfo';

    /**
     * API根路径
     * @var string
     */
   // protected $ApiBase = 'https://graph.qq.com/';
      protected  $ApiBase ='https://api.weixin.qq.com/';
      
    public function __construct($token = null) {
        parent::__construct($token);
        
        $config =  D('addons')->where(array('name'=>'SyncLogin'))->find();
        $config   =   json_decode($config['config'], true);
        
        if(is_weixin()){
            $this->GetRequestCodeURL = $this->GetRequestCodeURLM;
            $this->AppKey = $config['WeixinMKEY'];
            $this->AppSecret = $config['WeixinMSecret'];
        }
        else{
            $this->GetRequestCodeURL = $this->GetRequestCodeURLW;
            $this->AppKey = $config['WeixinKEY'];
            $this->AppSecret = $config['WeixinSecret'];
        }
    }
    
    public function getRequestCodeURL($callBack_query = ''){
        if(is_weixin()){
            return $this->getRequestCodeURLM($callBack_query);
        }
        else{
            return $this->getRequestCodeURLW($callBack_query);
        }
    }
      
    public function getRequestCodeURLW($callBack_query = ''){
        $state = mt_rand(000000, 999999);
        session('weixin_state', $state);
        $this->Authorize .= '&appid=' . $this->AppKey . '&state=' . $state . '#wechat_redirect';
        
        return parent::getRequestCodeURL($callBack_query);
    }
    
    public function getRequestCodeURLM(){
        $state = mt_rand(000000, 999999);
        session('weixin_state', $state);
        $this->config();
        $params = array(
            'appid' => $this->AppKey,
            'redirect_uri' => $this->Callback,
            'response_type' => 'code',
            'scope' => 'snsapi_userinfo',
            'state' => $state
        );
        
        return $this->GetRequestCodeURL . '?' . http_build_query($params) . '#wechat_redirect';
    }
    
    /**
     * 获取access_token
     * @param string $code 上一步请求到的code
     */
    public function getAccessToken($code, $extend = null){
        $params = array(
                'appid'     => $this->AppKey,
                'secret' => $this->AppSecret,
                'grant_type'    => $this->GrantType,
                'code'          => $code
        );

        $data = $this->http($this->GetAccessTokenURL, $params, 'POST');
        $this->Token = $this->parseToken($data, $extend);
        return $this->Token;
    }
      
    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    微信API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET', $multi = false){
        /* 微信调用公共参数 */
        $params = array(
            'access_token'       => $this->Token['access_token'],
            'openid'             => $this->openid()            
        );
        
        $data = $this->http($this->url($api), $this->param($params, $param), $method);
        return json_decode($data, true);
    }
    
    /**
     * 解析access_token方法请求后的返回值 
     * @param string $result 获取access_token的方法的返回值
     */
    protected function parseToken($result, $extend){
        $data = json_decode($result, true);
        if($data['access_token'] && $data['expires_in']){
            $this->Token    = $data;
            $data['openid'] = $this->openid();
            return $data;
        } else
            throw new Exception("获取微信 ACCESS_TOKEN 出错：{$result}");
    }
    
    /**
     * 获取当前授权应用的openid
     * @return string
     */
    public function openid(){
        $data = $this->Token;
        if(isset($data['openid']))
            return $data['openid'];
        elseif($data['access_token']){
            $data = $this->http($this->url('sns/oauth2/access_token'), array('access_token' => $data['access_token']));
            $data = json_decode(trim(substr($data, 9), " );\n"), true);
            if(isset($data['openid']))
                return $data['openid'];
            else
                throw new Exception("获取用户openid出错：{$data['error_description']}");
        } else {
            throw new Exception('没有获取到openid！');
        }
    }
}