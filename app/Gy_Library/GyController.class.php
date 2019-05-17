<?php

namespace Gy_Library;
use Think\Controller;
use Common\Util\GyRbac;
use Gy_Library\DBCont;
use Common\Lib\FlashError;
use Common\Lib\Flash;

class GyController extends Controller {

    protected function display($templateFile='',$charset='',$contentType='',$content='',$prefix=''){

        if(C('GY_TOKEN_ON') && !C('TOKEN_ON')){
            C('TOKEN_ON', true);
        }

        parent::display($templateFile, $charset, $contentType, $content, $prefix);
    }

    //检验是否重复提交表单，重复提交则原页面重定向
    protected  function autoCheckToken($url = ''){

        if(!empty($_POST)){
            $model = D(ucfirst($this->dbname));

            if(!$model->autoCheckToken($_POST)){
                if($url == ''){
                    redirect(U('/' . MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME));
                }
                else{
                    redirect($url);
                }
            }
        }
        C('TOKEN_ON', false);
        return true;
    }

    protected function _initialize(){

        //初始化查询条件，所有

        $this->dbname = $this->dbname ? $this->dbname : 'Common/' . CONTROLLER_NAME;


        //未使用ajax前，暂时使用
        //将后台菜单存入缓存
        if(in_array(strtolower(MODULE_NAME), C("BACKEND_MODULE"))){

            if(!isAdminLogin()){
                $this->redirect(C('USER_AUTH_GATEWAY'));
            }

            //非正常状态用户禁止登录后台
            $user_ent = D(C('USER_AUTH_MODEL'))->find(session(C('USER_AUTH_KEY')));
            if($user_ent['status'] != DBCont::NORMAL_STATUS){
                E('用户状态异常');
            }

            $menu = new \Common\Model\MenuModel();

            //顶部菜单栏
            $top_menu_list = $menu->getMenuList('top_menu');
            $this->assign('top_menu', $top_menu_list);
            $this->assign('current_module', strtolower(MODULE_NAME));

            $top_menu_id = 0;
            foreach($top_menu_list as $top_menu){
                if($top_menu['module'] == strtolower(MODULE_NAME)){
                    $top_menu_id = $top_menu['id'];
                    break;
                }
            }

            $menu_list = $menu->getMenuList('backend_menu', $top_menu_id);
            //要在左边栏显示的菜单
            $show_list  = array();


            $node = new \Common\Model\NodeModel();
            for ($i = 0; $i<count($menu_list);$i++){
                $node_map['status'] = DBCont::NORMAL_STATUS;
                $node_map['menu_id'] = $menu_list[$i]['id'];
                $node_map['level'] = DBCont::LEVEL_ACTION;
                $node_list = $node->getNodeList($node_map);

                $show_node_list = array();
                $add_flag = false;
                for($n = 0; $n<count($node_list); $n++){
                    $node_id = $node_list[$n]['id'];
                    if(GyRbac::checkAccessNodeId(session(C('USER_AUTH_KEY')), $node_id)){
                        $node_list[$n]['url'] = $this->_node_url($node_id);
                        $show_node_list[] = $node_list[$n];
                        $add_flag = true;
                    }
                }
                //只显示有权限操作的菜单项
                if($add_flag){

                    $menu_list[$i]['node_list'] = $show_node_list;
                    $show_list[] = $menu_list[$i];
                }
            }
            $backend_menu = $show_list;
            $this->assign('menu_list', $backend_menu);
        }

        if(!GyRbac::AccessDecision()){
            E(l('no_auth'));
        }

        $this->flashError();
        $this->flashInput();
    }

    private function flashInput(){
        if(IS_POST){
            $post_data = I('post.');
            foreach($post_data as $k => $v){
                Flash::set('qs_old_input.' . $k, $v);
            }
        }
    }

    private function flashError(){
        $this->errors = FlashError::all();
    }

    //生成节点的url地址
    private function _node_url($node_id){
        $node = new \Common\Model\NodeModel();
        $action = $node->find($node_id);
        if($action['url']){
            return $action['url'];
        }
        else{
            $controller = $node->find($action['pid']);
            $module = $node->find($controller['pid']);
            $url = U($module['name'] . '/' . $controller['name'] . '/' . $action['name']);
            return $url;
        }
    }

    protected function success($message = '', $jumpUrl = '', $ajax = false) {
        //$refer_url = I('get.refer_url');
//        show_bug($refer_url);
//        exit();
        //$jumpUrl = empty($jumpUrl) && !empty($refer_url) ? urldecode($refer_url) : $jumpUrl;

        parent::success($message, $jumpUrl, $ajax);
    }


}
