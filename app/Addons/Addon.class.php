<?php

namespace Addons;

abstract class Addon{

    protected $view = null;

    public $info                =   array();
    public $addon_path          =   '';
    public $config_file         =   '';
    public $custom_config       =   '';
    public $admin_list          =   array();
    public $custom_adminlist    =   '';
    public $access_url          =   array();
    protected $_error;

    public function __construct(){
        $this->view         =   \Think\Think::instance('Think\View');
        $this->addon_path   =   ADDON_PATH.$this->getName().'/';
        $TMPL_PARSE_STRING = C('TMPL_PARSE_STRING');
        $TMPL_PARSE_STRING['__ADDONS__'] = APP_PATH . '/Addons/'.$this->getName();
        C('TMPL_PARSE_STRING', $TMPL_PARSE_STRING);
        if(is_file($this->addon_path.'config.php')){
            $this->config_file = $this->addon_path.'config.php';
        }
    }
    
    final public function getError(){
        return $this->_error;
    }
    
    final public function setAdminFunctionList($function_name){
        array_push($this->_admin_function_list, $function_name);
    }
    
    final public function getAdminFunctionList(){
        return $this->_admin_function_list;
    }

    /**
     * 模板主题设置
     * @access protected
     * @param string $theme 模版主题
     * @return Action
     */
    final protected function theme($theme){
        $this->view->theme($theme);
        return $this;
    }

    //显示方法
    final protected function display($template=''){
        if($template == '')
            $template = CONTROLLER_NAME;
        echo ($this->fetch($template));
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return Action
     */
    final protected function assign($name,$value='') {
        $this->view->assign($name,$value);
        return $this;
    }


    //用于显示模板的方法
    final protected function fetch($templateFile = CONTROLLER_NAME){
        if(!is_file($templateFile)){
            $templateFile = $this->addon_path.$templateFile.C('TMPL_TEMPLATE_SUFFIX');
            if(!is_file($templateFile)){
                throw new \Exception("模板不存在:$templateFile");
            }
        }
        return $this->view->fetch($templateFile);
    }

    final public function getName(){
        $class = get_class($this);
        return substr($class,strrpos($class, '\\')+1, -5);
    }

    final public function checkInfo(){
        $info_check_keys = array('name','title','description','status','author','version');
        foreach ($info_check_keys as $value) {
            if(!array_key_exists($value, $this->info))
                return FALSE;
        }
        return TRUE;
    }
    
    final public function createHook($hook_name, $desc){
        $hook_ent = D('Hooks')->where(array('name' => $hook_name))->find();
        if(!$hook_ent){
            $data['name'] = $hook_name;
            $data['desc'] = $desc;
            $data['update_date'] = time();
            $data['status'] = 1;
            
            D('Hooks')->add($data);
        }
    }
    
    final public function delHook($hook_name){
        D('Hooks')->where(array('name' => $hook_name))->delete();
    }

    /**
     * 获取插件的配置数组
     */
    final public function getConfig($name=''){
        static $_config = array();
        if(empty($name)){
            $name = $this->getName();
        }
        if(isset($_config[$name])){
            return $_config[$name];
        }
        $config =   array();
        $map['name']    =   $name;
        $map['status']  =   1;
        $config  =   M('Addons')->where($map)->getField('config');
        if($config){
            $config   =   json_decode($config, true);
        }else{
            $temp_arr = include $this->config_file;
            foreach ($temp_arr as $key => $value) {
                if($value['type'] == 'group'){
                    foreach ($value['options'] as $gkey => $gvalue) {
                        foreach ($gvalue['options'] as $ikey => $ivalue) {
                            $config[$ikey] = $ivalue['value'];
                        }
                    }
                }else{
                    $config[$key] = $temp_arr[$key]['value'];
                }
            }
        }
        $_config[$name]     =   $config;
        return $config;
    }
    
    /**
     * 解析数据库语句函数
     * @param string $sql  sql语句   带默认前缀的
     * @param string $tablepre  自己的前缀
     * @return multitype:string 返回最终需要的sql语句
     */ 
    public function sql_split($sql, $tablepre){ 
        if ($tablepre != "onethink_") 
            $sql = str_replace("onethink_", $tablepre, $sql); 

        $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql); 
        if ($r_tablepre != $s_tablepre) 
            $sql = str_replace($s_tablepre, $r_tablepre, $sql); 

        $sql = str_replace("\r", "\n", $sql); 
        $ret = array(); 
        $num = 0; 
        $queriesarray = explode(";\n", trim($sql)); 
        unset($sql); 
        foreach ($queriesarray as $query) { 
            $ret[$num] = ''; 
            $queries = explode("\n", trim($query)); 
            $queries = array_filter($queries); 
            foreach ($queries as $query) { 
                $str1 = substr($query, 0, 1); 
                if ($str1 != '#' && $str1 != '-') 
                    $ret[$num] .= $query; 

            } 
            $num++; 
        } 
        return $ret; 
    }   
        
        /**
     * 获取插件所需的钩子是否存在，没有则新增
     * @param string $str  钩子名称
     * @param string $addons  插件名称
     * @param string $addons  插件简介
     */ 
    public function getisHook($str, $addons, $msg=''){ 
        $hook_mod = M('Hooks'); 
        $where['name'] = $str; 
        $gethook = $hook_mod->where($where)->find(); 
        if(!$gethook || empty($gethook) || !is_array($gethook)){
            $data['name'] = $str; 
            $data['description'] = $msg; 
            $data['type'] = 1; 
            $data['update_time'] = NOW_TIME; 
            $data['addons'] = $addons; 
            if( false !== $hook_mod->create($data) ){ 
                $hook_mod->add(); 

            } 
        } 
    } 
     /**
     * 删除钩子
     * @param string $hook  钩子名称
     */ 
    public function deleteHook($hook){
        $model = M('hooks'); 
        $condition = array( 'name' => $hook, ); 
        $model->where($condition)->delete(); 

    }

    //必须实现安装
    abstract public function install();

    //必须卸载插件方法
    abstract public function uninstall();
    
    //abstract public function displayConfig();
}
