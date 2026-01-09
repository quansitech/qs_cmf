<?php

namespace Admin\Controller;
use App\Models\Config;
use Gy_Library\DBCont;
use Gy_Library\GyListController;
use AntdAdmin\Component\Tabs;
use AntdAdmin\Component\Table;
use AntdAdmin\Component\Table\Pagination;
use AntdAdmin\Component\Form;

class ConfigController extends GyListController{
    
    public function index($status = DBCont::NORMAL_STATUS){
        

        // 设置Tab导航数据列表
        $user_status_list = DBCont::getStatusList();
        $tabs = new Tabs();
        foreach ($user_status_list as $key => $val) {
            $tabs->addTab($key, $val, null, U('indexTab', array('status' => $key)));
        }

        
        $tabs->render();

       
    }

    public function indexTab($status = DBCont::NORMAL_STATUS){
        // 搜索
        $keyword = I('keyword', '', 'string');
        
        $group_list = C('CONFIG_GROUP_LIST');
        $config = new Config();
        if(in_array($keyword, $group_list)){
            $flip_group_list = array_flip($group_list);
            $config->where(function($query) use ($keyword, $flip_group_list) {
                $query->where('name', 'like', '%'.$keyword.'%')
                      ->orWhere('title', 'like', '%'.$keyword.'%')
                      ->orWhere('group', $flip_group_list[$keyword]);
            });

        }
        else{
            $config->where(function($query) use ($keyword) {
                $query->where('name', 'like', '%'.$keyword.'%')
                      ->orWhere('title', 'like', '%'.$keyword.'%');
            });
        }
        
        
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        $configs = $config->where('status', $status)->paginate($per_page);

        $page = new Pagination($configs->currentPage(), $configs->perPage(), $configs->total());
        
        $form_type = C('FORM_ITEM_TYPE');

        $configs->through(function($item) use ($group_list, $form_type){
            $item->group = $group_list[$item->group];
            $item->type = $form_type[$item->type][0];

            return $item;
        });
       
        $data_list = collect($config->items())->map(function($item){
            return $item->toArray();
        });

        $table = new Table();
        $table->setMetaTitle('配置管理')
            ->actions(function(Table\ActionsContainer $actions){
                // $actions->button('添加')->modal((new Modal())->setUrl(U('add'))->setWidth(1080)->setTitle('新增配置'));
            })
            ->columns(function (Table\ColumnsContainer $container){
                $container->text('id', 'ID')
                    ->text('name', '配置名称')
                    ->text('title', '标题')
                    ->text('group', '分组')
                    ->text('type', '类型');
            })
            ->setDataSource($data_list)
            ->setPagination($page)
            ->render();

        
        // $builder = $builder->setMetaTitle('配置管理')  // 设置页面标题
        //                             ->addTopButton('addnew');   // 添加新增按钮
                                    
        
        // switch($status){
        //     case DBCont::NORMAL_STATUS;
        //         $builder = $builder->addTopButton('forbid');   // 添加禁用按钮
        //         break;
        //     case DBCont::FORBIDDEN_STATUS;
        //         $builder = $builder->addTopButton('resume');   // 添加启用按钮
        //         break;
        //     default:
        //         break;
        // }

        // $builder->addTopButton('delete')   // 添加删除按钮
        // ->setNID(61)
        // ->setTabNav($tab_list, $status)  // 设置页面Tab导航
        // ->addTableColumn('id', 'ID')
        // ->addTableColumn('name', '配置名称')
        // ->addTableColumn('title', '标题')
        // ->addTableColumn('group', '分组')
        // ->addTableColumn('type', '类型')
        // ->addTableColumn('right_button', '操作', 'btn')
        // ->setTableDataList($data_list)     // 数据列表
        // ->setTableDataPage($page->show())  // 数据列表分页
        // ->addRightButton('edit')           // 添加编辑按钮
        // ->addRightButton('forbid')         // 添加禁用/启用按钮
        // ->addRightButton('delete')         // 添加删除按钮
        // ->build();
    }
    
    // public function forbid(){
        
    //     $ids = I('ids');
    //     if(!$ids){
    //         $this->error('请选择要禁用的数据');
    //     }
    //     $r = parent::_forbid($ids);
    //     if($r !== false){
    //         sysLogs('配置项id: ' . $ids . ' 禁用');
    //         S('DB_CONFIG_DATA', null);
    //         $this->success('禁用成功', U(CONTROLLER_NAME . '/index'));
    //     }
    //     else{
    //         $this->error($this->_getError());
    //     }
    // }
    
    // public function resume(){
    //     $ids = I('ids');
    //     if(!$ids){
    //         $this->error('请选择要启用的数据');
    //     }
    //     $r = parent::_resume($ids);
    //     if($r !== false){
    //         sysLogs('配置项id: ' . $ids . ' 启用');
    //         S('DB_CONFIG_DATA', null);
    //         $this->success('启用成功', U(CONTROLLER_NAME . '/index'));
    //     }
    //     else{
    //         $this->error($this->_getError());
    //     }
        
    // }
    
    // public function delete(){
    //     $ids = I('ids');
    //     if(!$ids){
    //         $this->error('请选择要删除的数据');
    //     }
    //     $r = parent::_del($ids);
    //     if($r === false){
    //         $this->error($this->_getError());
    //     }
    //     else{
    //         sysLogs('删除配置项id：' . $ids);
    //         S('DB_CONFIG_DATA', null);
    //         $this->success('删除成功', U(MODULE_NAME . '/' . CONTROLLER_NAME . '/index'));
    //     }
    // }
    
    // public function add(){
    //     if (IS_POST) {
    //         parent::autoCheckToken();
    //         $data = I('post.');
        
    //         $config = Config::create($data);
    //         if($r === false){
    //             $this->error($config_model->getError());
    //         }
    //         else{
    //             sysLogs('新增配置项ID:' . $r);
    //             S('DB_CONFIG_DATA', null);
    //             $this->success('新增成功', U(CONTROLLER_NAME . '/index'));
    //         }
    //     }
    //     else {
    //         $form = new Form();
    //         $form->setMetaTitle('新增配置')
    //           ->setSubmitRequest('post', U('add'))
    //         $this->handleFormBuild($form)->render();
    //     }
    // }

    // protected function handleFormBuild(Form $form){
    //     $group_options = C('CONFIG_GROUP_LIST');

    //     $type_list = C('FORM_ITEM_TYPE');
    //     foreach($type_list as $key => $type){
    //         $type_options[$key] = $type[0];
    //     }

    //     $form->columns(function (Form\ColumnsContainer $columns) use ($type_options){
    //         $columns->text('name', '配置名称')->setTips('用于C函数调用，只能使用英文+下划线且不能重复');
    //         $columns->text('title', '配置标题')->setTips('用于后台显示的配置标题');
    //         $columns->text('sort', '排序')->setTips('显示顺序');
    //         $columns->select('type', '配置分类')->setValueEnum($type_options)->setTips('系统会根据不同类型解析配置值');
    //         $columns->select('group', '配置分组')->setValueEnum($group_options)->setTips('配置信息的分组|不分组则不会显示该配置项');
    //         $columns->textarea('value', '配置值');
    //         $columns->textarea('extra', '配置项')->setTips('只有当配置类型为复选框、下拉框、单选按钮时需要填写');
    //         $columns->textarea('remark', 'tips')->setTips('配置项说明提示');
    //     })
    //     return $form;
    // }
    
    // public function edit($id){
    //     if (IS_POST) {
    //         parent::autoCheckToken();
    //         $data = I('post.');
    //         $config_model = D('Config');
    //         $r = $config_model->createSave($data);

    //         if($r === false){
    //             $this->error($config_model->getError());
    //         }
    //         else{
    //             sysLogs('修改配置项ID:' . $id);
    //             S('DB_CONFIG_DATA', null);
    //             $this->success('修改成功', U('index'));
    //         }
    //     } else {
    //         // 使用FormBuilder快速建立表单页面。
    //         $type_list = C('FORM_ITEM_TYPE');
    //         foreach($type_list as $key => $type){
    //             $type_options[$key] = $type[0];
    //         }
            
    //         $group_options = C('CONFIG_GROUP_LIST');
            
    //         $form_data = D('Config')->getOne($id);
            
    //         $builder = new \Qscmf\Builder\FormBuilder();
    //         $builder->setMetaTitle('编辑配置') //设置页面标题
    //                 ->setNID(61)
    //                 ->setPostUrl(U('edit'))    //设置表单提交地址
    //                 ->addFormItem('id', 'hidden', 'ID')
    //                 ->addFormItem('name', 'text', '配置名称', '用于C函数调用，只能使用英文+下划线且不能重复')
    //                 ->addFormItem('title', 'text', '配置标题', '用于后台显示的配置标题')
    //                 ->addFormItem('sort', 'text', '排序', '显示顺序')
    //                 ->addFormItem('type', 'select', '配置类型', '系统会根据不同类型解析配置值', $type_options)
    //                 ->addFormItem('group', 'select', '配置分组', '配置信息的分组|不分组则不会显示该配置项', $group_options)
    //                 ->addFormItem('value', 'textarea', '配置值')
    //                 ->addFormItem('extra', 'textarea', '配置项', '只有当配置类型为复选框、下拉框、单选按钮时需要填写')
    //                 ->addFormItem('remark', 'textarea', 'tips', '配置项说明提示')
    //                 ->setFormData($form_data)
    //                 ->build();
    //     }
    // }
    
    // public function setting($group = 1){
    //     $config = new Config();
    //     if(!empty($_POST)){
    //         $conf_arr = I('config');
    //         if($conf_arr && is_array($conf_arr)){
    //             foreach ($conf_arr as $name => $value) {
    //                 $r = $config->updateConfig($name, $value);
    //                 if($r === false){
    //                     $this->error($config->getError());
    //                 }
    //             }
    //             S('DB_CONFIG_DATA', null);
    //             sysLogs('修改系统配置');
    //             $this->success('修改配置成功', U(CONTROLLER_NAME . '/setting', ['group' => $group]));
    //         }
    //     }else{
    //         $group_list   =   C('CONFIG_GROUP_LIST');
            
    //         foreach($group_list as $key => $val){
    //             $tab_list[$key]['title'] = $val;
    //             $tab_list[$key]['href'] = U('setting', array('group' => $key));
    //         }
            
    //         $map['status'] = DBCont::NORMAL_STATUS;
    //         $map['group'] = $group;
    //         $data_list   = $config->getConfigList($map);
            
    //         // 使用FormBuilder快速建立表单页面。
    //         $builder = new \Qscmf\Builder\FormBuilder();
    //         $form_data = array();

    //         foreach($data_list as $data){
    //             if(in_array($data['type'], ['file', 'ueditor'])){
    //                 $builder->addFormItem('config[' . $data['name'] . ']', $data['type'], $data['title'], $data['remark'], '', '', $data['extra']);
    //             }
    //             else{
    //                 $builder->addFormItem('config[' . $data['name'] . ']', $data['type'], $data['title'], $data['remark'], parse_config_attr($data['extra']));
    //             }
    //             $form_data['config[' . $data['name'] . ']'] = $data['value'];
    //         }
            
    //         $builder->setMetaTitle('系统设置')       // 设置页面标题
    //                 ->setNID(69)
    //             ->setTabNav($tab_list, $group)  // 设置Tab按钮列表
    //                 ->setFormData($form_data)
    //                 ->setPostUrl(U('setting', ['group' => $group]))    // 设置表单提交地址
    //                 //->setExtraItems($data_list)     // 直接设置表单数据
    //                 ->build();
    //     }
    // }
    
}
