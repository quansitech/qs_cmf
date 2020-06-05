<?php

namespace Common\Coder\ContentCate;
use Common\Coder\Coder;

class ContentCateCoder extends Coder{

    public function __construct() {
        parent::__construct();
        $this->_name = '内容分类快速生成器';
        
        $this->_desc = '可快速生成内容及分类的内容管理模块';
        
        $this->_namespace = __NAMESPACE__;
        
        $this->_view = CODER_DIR . '/ContentCate/View/index.html';
    }
    
    public function displayVew($log_id = ''){
        $this->assign('meta_title', $this->getName());
        $this->assign('id', 'ContentCate');
        
        if($log_id){
            $ent = D('CoderLog')->getOne($log_id);
            $this->assign('coder_log', json_decode($ent['content'], true));
            $this->assign('log_id', $log_id);
        }

        $this->display($this->getView());
    }
    
    public function logList(){
        $model = D('CoderLog');
        $map['coder_name'] = 'ContentCate';
        $count = $model->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        
        $data_list = $model->getListForPage($map, $page->nowPage, $page->listRows, 'id desc');
        
        $builder = new \Qscmf\Builder\ListBuilder();
        
        $builder = $builder->setMetaTitle($this->_name . '生成记录')         
        ->addTopButton('delete');                
        $builder->setNID(301)
        ->addTableColumn('name', '控制器名称', '', '', false)->addTableColumn('create_date', '创建时间',  'fun', 'date("Y-m-d H:i:s",__data_id__)', false) ->addTableColumn('right_button', '操作', 'btn')
        ->setTableDataList($data_list)     
        ->setTableDataPage($page->show())
        ->addRightButton('edit')           
        ->addRightButton('delete')        
        ->display();
    }
    
    public function generate($save_flag = ''){
        $this->dbname = '';
        if(IS_POST){
            parent::autoCheckToken();
            
            $data = I('post.');
            
            if($data['log_id']){
                $log_data = D('CoderLog')->getOne($data['log_id']);
            }
            $log_data['coder_name'] = 'ContentCate';
            $log_data['content'] = json_encode($data);
            $log_data['create_date'] = time();
            $log_data['name'] = $data['content_controller_name'];
            if(isset($log_data['id'])){
                D('CoderLog')->save($log_data);
            }
            else{
                D('CoderLog')->add($log_data);
            }
            
            if($save_flag){
                $this->success('保存成功', U('admin/coder/coderLog', array('id' => 'ContentCate')));
                return;
            }
            
            $content_show_columns = array();
            $content_select_search = array();
            $content_like_search = array();
            $content_add_columns = array();
            $content_columns = array();
            
            $content_table_name = $data['content_table_name'];
            
            $this->_makeTable($data, $content_table_name, 'content_',$content_columns, $content_show_columns, $content_select_search, $content_like_search, $content_add_columns);
            
            $cate_show_columns = array();
            $cate_select_search = array();
            $cate_like_search = array();
            $cate_add_columns = array();
            $cate_columns = array();
            $cate_table_name = $data['content_table_name'] . '_cate';
            $this->_makeTable($data, $cate_table_name, 'cate_',$cate_columns, $cate_show_columns, $cate_select_search, $cate_like_search, $cate_add_columns);

            $content_model_name = '';
            $this->_createModel($content_table_name, $content_model_name, $content_add_columns, $content_columns);
            $cate_model_name = '';
            $this->_createModel($cate_table_name, $cate_model_name, $cate_add_columns, $cate_columns);

            $menu_data['title'] = $data['menu_title'];
            $menu_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
            $menu_data['sort'] = D('Menu')->Max('sort') + 1;
            $menu_data['type'] = 'backend_menu';
            $menu_data['icon'] = $data['menu_icon'];
            $menu_data['pid'] = 0;
            $menu_data['level'] = 1;
            $menu_id = D('Menu')->add($menu_data);
            $content_action_id = $this->_createNode($data, $menu_id, 'content');
            $this->_createController(ucfirst($data['content_controller_name']), $content_model_name, '内容', $content_action_id, array('action' => $data['action'], 'btn_title' => $data['save_btn_title']), $content_show_columns, $content_add_columns, $content_select_search, $content_like_search);
            
            $cate_action_id = $this->_createNode($data, $menu_id, 'cate');
            $this->_createController(ucfirst($data['content_controller_name']) . 'Cate', $cate_model_name, '分类', $cate_action_id, array('action' => $data['action'], 'btn_title' => $data['save_btn_title']), $cate_show_columns, $cate_add_columns, $cate_select_search, $cate_like_search, "tree");
            
            $this->success('成功');
        }
    }
    
    private function _createNode($data, $menu_id, $type){
        $controller_node_data['name'] = $type == 'content' ? $data['content_controller_name'] : $data['content_controller_name'] . 'Cate';
        $controller_node_data['title'] = $type == 'content' ? $data['menu_title'] : $data['menu_title'] . '分类';
        $controller_node_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $controller_node_data['pid'] = 1;
        $controller_node_data['level'] = \Gy_Library\DBCont::LEVEL_CONTROLLER;
        $controller_id = D('Node')->add($controller_node_data);

        $action_node_data['name'] = 'index';
        $action_node_data['title'] = $type == 'content' ? '内容管理' : '分类管理';
        $action_node_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $action_node_data['sort'] = $type == 'content' ? 1 : 2;
        $action_node_data['pid'] = $controller_id;
        $action_node_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
        $action_node_data['menu_id'] = $menu_id;
        $action_id = D('Node')->add($action_node_data);

        $add_data['name'] = 'add';
        $add_data['title'] = '新增';
        $add_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $add_data['pid'] = $controller_id;
        $add_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
        D('Node')->add($add_data);

        $edit_data['name'] = 'edit';
        $edit_data['title'] = '修改';
        $edit_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $edit_data['pid'] = $controller_id;
        $edit_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
        D('Node')->add($edit_data);

        $del_data['name'] = 'delete';
        $del_data['title'] = '删除';
        $del_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $del_data['pid'] = $controller_id;
        $del_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
        D('Node')->add($del_data);
        
        $forbid_data['name'] = 'forbid';
        $forbid_data['title'] = '禁用';
        $forbid_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $forbid_data['pid'] = $controller_id;
        $forbid_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
        D('Node')->add($forbid_data);
        
        $resume_data['name'] = 'resume';
        $resume_data['title'] = '启用';
        $resume_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $resume_data['pid'] = $controller_id;
        $resume_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
        D('Node')->add($resume_data);

        if($data['action'] == 'save'){
            $save_data['name'] = 'save';
            $save_data['title'] = $data['save_btn_title'];
            $save_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
            $save_data['pid'] = $controller_id;
            $save_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
            D('Node')->add($save_data);
        }
        
        return $action_id;
    }
    
    private function _makeTable($data, $table_name,$prefix, &$columns, &$content_show_columns, &$content_select_search, &$content_like_search, &$content_add_columns){
        $table_maker = new \Gy_Library\TableMaker();

        $table_maker->setTableName($table_name);

        $add_n = 0;
        $list_n = 0;
        foreach($data[$prefix . 'column_name'] as $k => $v){
            $column['name'] = $v;
            $column['column_type'] = $data[$prefix . 'column_type'][$k];
            $column['comment'] = $data[$prefix . 'comment'][$k];
            $column['require'] = $data[$prefix . 'require'][$k];
            $table_maker->addColumns($column);
            
            $columns[] = $column;

            if($data[$prefix . 'list_show'][$k] == 1){
                $content_show_columns[] = array('name' => $v, 
                    'type' => $data[$prefix . 'list_show_type'][$list_n] ,
                    'title' => $data[$prefix . 'comment'][$k], 
                    'fun' => $data[$prefix . 'list_show_fun'][$list_n],
                    'edit' => ($data[$prefix . 'list_edit'][$list_n] == 1)
                        );
                if($data[$prefix . 'list_show_select_search'][$list_n] == 1){
                    $content_select_search[] = array(
                        'title' => $data[$prefix . 'comment'][$k],
                        'name' => $v,
                        'fun' => $data[$prefix . 'list_show_select_options_fun'][$list_n]
                    );
                }
                if($data[$prefix . 'list_show_like_search'][$list_n] == 1){
                    $content_like_search[$v] = $data[$prefix . 'comment'][$k];
                }

                $list_n++;
            }
            if($data[$prefix . 'add_show'][$k] == 1){
                $content_add_columns[] = array(
                    'name' => $v, 
                    'type' => $data[$prefix . 'add_show_type'][$add_n], 
                    'title' => $data[$prefix . 'comment'][$k], 
                    'options_fun' => $data[$prefix . 'add_show_options_fun'][$add_n],
                    'extra_attr' => $data[$prefix . 'add_show_extra_attr'][$add_n],
                    'default' => $data[$prefix . 'add_show_default'][$add_n],
                    'tips' => $data[$prefix. 'add_show_tips'][$add_n]
                );
                $add_n++;
            }
        }
        if($table_maker->make() === false){
            $this->error($table_maker->getError());
        }
    }
    
    private function _createModel($table_name, &$model_name, $add_columns, $columns){
        //创建model begin

        if(strpos($table_name, '_') !== false){
            $arr = explode('_', $table_name);
            $table_name = '';
            foreach($arr as $v){
                $table_name .= ucfirst($v);
            }
        }
        $model_name = ucfirst($table_name);
        $view = new \Think\View();
        $view->assign('table_name', $model_name);
        $view->assign('add_columns', $add_columns);
        $view->assign('columns', $columns);

        $content = $view->fetch(CODER_DIR . '/ContentCate/tpl/model.tpl');
        $content = "<?php
{$content}";

        $file = APP_PATH . 'Common/Model/' . $model_name . 'Model' . EXT;
        file_put_contents($file,$content);

        //创建model end
    }
    
    private function _createController($controller_name, $model_name, $list_title, $nid, $save_action_arr, $show_columns, $add_columns, $select_search, $like_search, $tree = ''){
        //创建controller begin
        $controller_view = new \Think\View();
        $controller_view->assign('controller_name', $controller_name);
        $controller_view->assign('model_name', $model_name);
        $controller_view->assign('list_title', $list_title);
        $controller_view->assign('nid', $nid);
        $controller_view->assign('save_action', $save_action_arr);
        $controller_view->assign('show_columns', $show_columns);
        $controller_view->assign('add_columns', $add_columns);
        $controller_view->assign('select_search', $select_search);
        $controller_view->assign('tree', $tree);
        $like_search_arr = array();
        foreach($like_search as $k=>$v){
            $like_search_arr[] = "'{$k}'=>'{$v}'";
        }
        if($like_search_arr){
            $like_search_str = implode(',', $like_search_arr);
            $like_search_str = "array({$like_search_str})";
        }
        $controller_view->assign('like_search', $like_search_str);

        $controller_content = $controller_view->fetch(CODER_DIR . '/ContentCate/tpl/controller.tpl');

        $controller_content = "<?php
{$controller_content}";

        $file = APP_PATH . 'Admin/Controller/' . $controller_name . 'Controller' . EXT;
        file_put_contents($file,$controller_content);
        //创建controller end
    }
}

