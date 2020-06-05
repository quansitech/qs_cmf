<?php

namespace Common\Coder\ADE;
use Common\Coder\Coder;

class ADECoder extends Coder{

    public function __construct() {
        parent::__construct();
        $this->_name = '增删查改快速生成器';
        
        $this->_desc = '可快速生成带有新增删除修改启用禁用的列表功能';
        
        $this->_namespace = str_replace('\\', '/', __NAMESPACE__);
        
        $this->_view = CODER_DIR . '/ADE/View/index.html';
    }
    
    public function displayVew($log_id = ''){
        $this->assign('meta_title', $this->getName());
        $this->assign('id', 'ADE');
        
        if($log_id){
            $ent = D('CoderLog')->getOne($log_id);
            $this->assign('coder_log', json_decode($ent['content'], true));
            $this->assign('log_id', $log_id);
        }
        
        $this->display($this->getView());
    }
    
    public function logList(){
        $model = D('CoderLog');
        $map['coder_name'] = 'ADE';
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
            $log_data['coder_name'] = 'ADE';
            $log_data['content'] = json_encode($data);
            $log_data['create_date'] = time();
            $log_data['name'] = $data['controller_name'];
            if(isset($log_data['id'])){
                D('CoderLog')->save($log_data);
            }
            else{
                D('CoderLog')->add($log_data);
            }
            
            if($save_flag){
                $this->success('保存成功', U('admin/coder/coderLog', array('id' => 'ADE')));
                return;
            }
            
            //创建表 begin
            $table_maker = new \Gy_Library\TableMaker();
            
            $table_maker->setTableName($data['table_name']);
            
            $show_columns = array();
            $add_columns = array();
            $select_search = array();
            $like_search = array();
            $columns = array();
            $add_n = 0;
            $list_n = 0;
            foreach($data['column_name'] as $k => $v){
                $column['name'] = $v;
                $column['column_type'] = $data['column_type'][$k];
                $column['comment'] = $data['comment'][$k];
                $column['require'] = $data['require'][$k];
                $table_maker->addColumns($column);
                
                $columns[] = $column;
                
                if($data['list_show'][$k] == 1){
                    $show_columns[] = array(
                        'name' => $v,
                        'type' => $data['list_show_type'][$list_n],
                        'title' => $data['comment'][$k], 
                        'fun' => $data['list_show_fun'][$list_n],
                        'edit' => ($data['list_edit'][$list_n] == 1)
                    );
                    if($data['list_show_select_search'][$list_n] == 1){
                        $select_search[] = array(
                            'title' => $data['comment'][$k],
                            'name' => $v,
                            'fun' => $data['list_show_select_options_fun'][$list_n]
                        );
                    }
                    if($data['list_show_like_search'][$list_n] == 1){
                        $like_search[$v] = $data['comment'][$k];
                    }
                    $list_n++;
                }

                if($data['add_show'][$k] == 1){
                    $add_columns[] = array(
                        'name' => $v,
                        'type' => $data['add_show_type'][$add_n],
                        'title' => $data['comment'][$k],
                        'options_fun' => $data['add_show_options_fun'][$add_n],
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
            //创建表 end
            
            //创建model begin
            $view = new \Think\View();
            $view->assign('table_name', parse_name($data['table_name'], 1));
            $view->assign('add_columns', $add_columns);
            $view->assign('columns', $columns);
            
            $content = $view->fetch(CODER_DIR . '/ADE/tpl/model.tpl');
            $content = "<?php
{$content}";
            
            $file = APP_PATH . 'Common/Model/' . parse_name($data['table_name'], 1) . 'Model' . EXT;
            file_put_contents($file,$content);
            
            //创建model end
            
            $nid = $this->_createNode($data);
            
            //创建controller begin
            $controller_view = new \Think\View();
            $controller_view->assign('controller_name', ucfirst($data['controller_name']));
            $controller_view->assign('model_name', parse_name($data['table_name'], 1));
            $controller_view->assign('list_title', $data['list_title']);
            $controller_view->assign('nid', $nid);
            $controller_view->assign('action', array_flip($data['action']));
            $controller_view->assign('show_columns', $show_columns);
            $controller_view->assign('add_columns', $add_columns);
            $controller_view->assign('select_search', $select_search);
            $like_search_arr = array();
            foreach($like_search as $k=>$v){
                $like_search_arr[] = "'{$k}'=>'{$v}'";
            }
            if($like_search_arr){
                $like_search_str = implode(',', $like_search_arr);
                $like_search_str = "array({$like_search_str})";
            }
            $controller_view->assign('like_search', $like_search_str);
            
            $controller_content = $controller_view->fetch(CODER_DIR . '/ADE/tpl/controller.tpl');
            
            $controller_content = "<?php
{$controller_content}";
            
            $file = APP_PATH . 'Admin/Controller/' . ucfirst($data['controller_name']) . 'Controller' . EXT;
            file_put_contents($file,$controller_content);
            //创建controller end
            $this->success('成功');
        }
    }
    
    private function _createNode($data){
        $controller_node_data['name'] = $data['controller_name'];
        $controller_node_data['title'] = $data['list_title'] . '管理';
        $controller_node_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $controller_node_data['pid'] = 1;
        $controller_node_data['level'] = \Gy_Library\DBCont::LEVEL_CONTROLLER;
        $controller_id = D('Node')->add($controller_node_data);

        $action_node_data['name'] = 'index';
        $action_node_data['title'] = $data['list_title'] . '列表';
        $action_node_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $action_node_data['sort'] = 0;
        $action_node_data['pid'] = $controller_id;
        $action_node_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
        $action_node_data['menu_id'] = $data['menu_id'];
        $action_id = D('Node')->add($action_node_data);

        if(in_array('add', $data['action'])){
            $add_data['name'] = 'add';
            $add_data['title'] = '新增';
            $add_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
            $add_data['pid'] = $controller_id;
            $add_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
            D('Node')->add($add_data);
        }

        if(in_array('edit', $data['action'])){
            $edit_data['name'] = 'edit';
            $edit_data['title'] = '修改';
            $edit_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
            $edit_data['pid'] = $controller_id;
            $edit_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
            D('Node')->add($edit_data);
        }

        if(in_array('delete', $data['action'])){
            $del_data['name'] = 'delete';
            $del_data['title'] = '删除';
            $del_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
            $del_data['pid'] = $controller_id;
            $del_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
            D('Node')->add($del_data);
        }
        
        if(in_array('forbid_resume', $data['action'])){
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
        }

        if(in_array('save', $data['action'])){
            $save_data['name'] = 'save';
            $save_data['title'] = '保存';
            $save_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
            $save_data['pid'] = $controller_id;
            $save_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
            D('Node')->add($save_data);
        }
        
        return $action_id;
    }
}

