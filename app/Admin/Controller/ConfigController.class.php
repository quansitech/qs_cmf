<?php

namespace Admin\Controller;
use App\Models\Config;
use Gy_Library\DBCont;
use Gy_Library\GyListController;
use AntdAdmin\Component\Tabs;
use AntdAdmin\Component\Table;
use AntdAdmin\Component\Table\Pagination;
use AntdAdmin\Component\Form;
use AntdAdmin\Component\Modal\Modal;
class ConfigController extends GyListController{
    
    public function index(){
        redirect(U('indexTab', ['status' => DBCont::NORMAL_STATUS]));
    }

    public function indexTab($status = DBCont::NORMAL_STATUS){
        // 搜索
        $search_name = I('name', '', 'string');
        $search_title = I('title', '', 'string');
        $search_group = I('group', '', 'string');
        $search_type = I('type', '', 'string');

        $group_list = C('CONFIG_GROUP_LIST');
        $form_type = C('FORM_ITEM_TYPE');
        $query = Config::query();

        $search_id = I('id', '', 'string');
        if(!qsEmpty($search_id)){
            $query->where('id', $search_id);
        }
        if(!qsEmpty($search_name)){
            $query->where('name', 'like', '%'.$search_name.'%');
        }
        if(!qsEmpty($search_title)){
            $query->where('title', 'like', '%'.$search_title.'%');
        }
        if(!qsEmpty($search_group)){
            if(!is_numeric($search_group) && in_array($search_group, $group_list)){
                $search_group = array_flip($group_list)[$search_group];
            }
            $query->where('group', $search_group);
        }
        if(!qsEmpty($search_type)){
            $type_options = collect($form_type)->mapWithKeys(function($v, $k){ return [$k => $v[0]]; })->toArray();
            if(!is_numeric($search_type) && in_array($search_type, $type_options)){
                $search_type = array_flip($type_options)[$search_type];
            }
            $query->where('type', $search_type);
        }
        
        
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        $configs = $query->where('status', $status)->paginate($per_page);

        $page = new Pagination($configs->currentPage(), $configs->perPage(), $configs->total());
        
        $form_type = C('FORM_ITEM_TYPE');

        $configs->through(function($item) use ($group_list, $form_type){
            $item->group = $group_list[$item->group];
            $item->type = $form_type[$item->type][0];

            return $item;
        });
       
        $data_list = collect($configs->items())->map(function($item){
            return $item->toArray();
        });

        $table = new Table();
        $table->setMetaTitle('配置管理')
            ->setSearch(true)
            ->actions(function(Table\ActionsContainer $actions) use ($status){
                $actions->button('添加')->modal((new Modal())->setUrl(U('add'))->setWidth(1080)->setTitle('新增配置'));
                if($status == DBCont::NORMAL_STATUS){
                    $actions->forbid();
                }else{
                    $actions->resume();
                }
                $actions->delete();
            })
            ->columns(function (Table\ColumnsContainer $container) use ($group_list, $form_type){
                $container->text('id', 'ID');
                $container->text('name', '配置名称');
                $container->text('title', '标题');
                $container->select('group', '分组')->setValueEnum($group_list);
                $container->select('type', '类型')->setValueEnum(collect($form_type)->mapWithKeys(function($v, $k){ return [$k => $v[0]]; })->toArray());
                $container->action('', '操作')->actions(function(Table\ColumnType\ActionsContainer $container){
                    $container->link('编辑')->setHref(U('edit', ['id' => '__id__']));
                    $container->forbid();
                    $container->delete();
                });
            })
            ->setDataSource($data_list)
            ->setPagination($page);

        $tabs = new Tabs();
        $user_status_list = DBCont::getStatusList();
        foreach ($user_status_list as $key => $val) {
            if ($key == $status) {
                $tabs->addTab('tab_' . $key, $val, $table);
            } else {
                $tabs->addTab('tab_' . $key, $val, null, U('indexTab', ['status' => $key]));
            }
        }
        $tabs->setDefaultActiveKey('tab_' . $status);
        $this->setActiveNid(getNid(MODULE_NAME, CONTROLLER_NAME, 'Index'));
        $tabs->setMetaTitle('配置管理')
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
    
    public function forbid(){

        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要禁用的数据');
        }
        $r = Config::whereIn('id', is_array($ids) ? $ids : explode(',', $ids))->update(['status' => DBCont::FORBIDDEN_STATUS]);
        if($r !== false){
            sysLogs('配置项id: ' . (is_array($ids) ? implode(',', $ids) : $ids) . ' 禁用');
            S('DB_CONFIG_DATA', null);
            $this->success('禁用成功', U(CONTROLLER_NAME . '/index'));
        }
        else{
            $this->error('禁用失败');
        }
    }

    public function resume(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要启用的数据');
        }
        $r = Config::whereIn('id', is_array($ids) ? $ids : explode(',', $ids))->update(['status' => DBCont::NORMAL_STATUS]);
        if($r !== false){
            sysLogs('配置项id: ' . (is_array($ids) ? implode(',', $ids) : $ids) . ' 启用');
            S('DB_CONFIG_DATA', null);
            $this->success('启用成功', U(CONTROLLER_NAME . '/index'));
        }
        else{
            $this->error('启用失败');
        }
    }
    
    public function delete(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要删除的数据');
        }
        $r = Config::destroy(explode(',', $ids));
        if($r === false){
            $this->error('删除失败');
        }
        else{
            sysLogs('删除配置项id：' . $ids);
            S('DB_CONFIG_DATA', null);
            $this->success('删除成功', U(MODULE_NAME . '/' . CONTROLLER_NAME . '/index'));
        }
    }
    
    public function add(){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');
            $data['value'] = $data['value'] ?? '';
            $data['extra'] = $data['extra'] ?? '';
            $data['remark'] = $data['remark'] ?? '';
            $data['status'] = $data['status'] ?? DBCont::NORMAL_STATUS;

            $config = Config::create($data);
            if(!$config){
                $this->error('新增失败');
            }
            else{
                sysLogs('新增配置项ID:' . $config->id);
                S('DB_CONFIG_DATA', null);
                $this->success('新增成功', U(CONTROLLER_NAME . '/index'));
            }
        }
        else {
            $form = new Form();
            $form->setMetaTitle('新增配置')
              ->setSubmitRequest('post', U('add'));
            $this->handleFormBuild($form)->render();
        }
    }

    protected function handleFormBuild(Form $form){
        $group_options = C('CONFIG_GROUP_LIST');

        $type_list = C('FORM_ITEM_TYPE');
        foreach($type_list as $key => $type){
            $type_options[$key] = $type[0];
        }

        $form->columns(function (Form\ColumnsContainer $columns) use ($type_options, $group_options){
            $columns->text('name', '配置名称')->setTips('用于C函数调用，只能使用英文+下划线且不能重复');
            $columns->text('title', '配置标题')->setTips('用于后台显示的配置标题');
            $columns->text('sort', '排序')->setTips('显示顺序');
            $columns->select('type', '配置分类')->setValueEnum($type_options)->setTips('系统会根据不同类型解析配置值');
            $columns->select('group', '配置分组')->setValueEnum($group_options)->setTips('配置信息的分组|不分组则不会显示该配置项');
            $columns->textarea('value', '配置值');
            $columns->textarea('extra', '配置项')->setTips('只有当配置类型为复选框、下拉框、单选按钮时需要填写');
            $columns->textarea('remark', 'tips')->setTips('配置项说明提示');
        });
        $form->actions(function (Form\ActionsContainer $actions){
            $actions->button('提交')->submit();
            $actions->button('重置')->reset();
        });
        return $form;
    }
    
    public function edit($id){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');
            unset($data['id']);
            $r = Config::find($id)?->update($data);

            if($r === false){
                $this->error('修改失败');
            }
            else{
                sysLogs('修改配置项ID:' . $id);
                S('DB_CONFIG_DATA', null);
                $this->success('修改成功', U('index'));
            }
        } else {
            $config = Config::find($id);
            if (!$config) {
                $this->error('配置项不存在');
            }
            $form_data = $config->toArray();

            $form = new Form();
            $form->setMetaTitle('编辑配置')
              ->setSubmitRequest('post', U('edit', ['id' => $id]))
              ->setInitialValues($form_data);
            $this->handleFormBuild($form)->render();
        }
    }
    
    public function setting($group = 1){
        if(!empty($_POST)){
            $conf_arr = I('config');
            if($conf_arr && is_array($conf_arr)){
                foreach ($conf_arr as $name => $value) {
                    $r = Config::updateConfig($name, $value);
                    if($r === false){
                        $this->error('修改配置项 ' . $name . ' 失败');
                    }
                }
                S('DB_CONFIG_DATA', null);
                sysLogs('修改系统配置');
                $this->success('修改配置成功', U(CONTROLLER_NAME . '/setting', ['group' => $group]));
            }
        }else{
            $group_list   =   C('CONFIG_GROUP_LIST');
            
            foreach($group_list as $key => $val){
                $tab_list[$key]['title'] = $val;
                $tab_list[$key]['href'] = U('setting', array('group' => $key));
            }
            
            $map['status'] = DBCont::NORMAL_STATUS;
            $map['group'] = $group;
            $data_list   = Config::getConfigList($map);
            
            // 使用FormBuilder快速建立表单页面。
            $builder = new \Qscmf\Builder\FormBuilder();
            $form_data = array();

            foreach($data_list as $data){
                if(in_array($data['type'], ['file', 'ueditor'])){
                    $builder->addFormItem('config[' . $data['name'] . ']', $data['type'], $data['title'], $data['remark'], '', '', $data['extra']);
                }
                else{
                    $builder->addFormItem('config[' . $data['name'] . ']', $data['type'], $data['title'], $data['remark'], parse_config_attr($data['extra']));
                }
                $form_data['config[' . $data['name'] . ']'] = $data['value'];
            }
            
            $builder->setMetaTitle('系统设置')       // 设置页面标题
                    ->setNID(69)
                ->setTabNav($tab_list, $group)  // 设置Tab按钮列表
                    ->setFormData($form_data)
                    ->setPostUrl(U('setting', ['group' => $group]))    // 设置表单提交地址
                    //->setExtraItems($data_list)     // 直接设置表单数据
                    ->build();
        }
    }
    
}
