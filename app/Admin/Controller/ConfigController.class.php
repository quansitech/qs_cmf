<?php

namespace Admin\Controller;
use Gy_Library\DBCont;
use Gy_Library\GyListController;

class ConfigController extends GyListController{
    
    public function index($status = DBCont::NORMAL_STATUS){
        // 搜索
        $keyword = I('keyword', '', 'string');
        $condition = array('like','%'.$keyword.'%');
        
        $group_list = C('CONFIG_GROUP_LIST');
        if(in_array($keyword, $group_list)){
            $flip_group_list = array_flip($group_list);
            $map['name|title|group'] = array(
                $condition,
                $condition,
                $flip_group_list[$keyword],
                '_multi'=>true
            );
        }
        else{
            $map['name|title'] = array(
                $condition,
                $condition,
                '_multi'=>true
            );
        }
        
        $map['status'] = $status;

        $config_model = D('Config');
        $count = $config_model->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        
        $data_list = $config_model->getListForPage($map, $page->nowPage, $page->listRows, 'sort desc');
        $form_type = C('FORM_ITEM_TYPE');
        foreach ($data_list as &$v){
            $v['group'] = $group_list[$v['group']];
            $v['type'] = $form_type[$v['type']][0];
        }
       

        // 设置Tab导航数据列表
        $user_status_list = DBCont::getStatusList();
        foreach ($user_status_list as $key => $val) {
            $tab_list[$key]['title'] = $val;
            $tab_list[$key]['href']  = U('index', array('status' => $key));
        }

        // 使用Builder快速建立列表页面。
        $builder = new \Qscmf\Builder\ListBuilder();
        
        $builder = $builder->setMetaTitle('配置管理')  // 设置页面标题
                                    ->addTopButton('addnew');   // 添加新增按钮
                                    
        
        switch($status){
            case DBCont::NORMAL_STATUS;
                $builder = $builder->addTopButton('forbid');   // 添加禁用按钮
                break;
            case DBCont::FORBIDDEN_STATUS;
                $builder = $builder->addTopButton('resume');   // 添加启用按钮
                break;
            default:
                break;
        }

        $builder->addTopButton('delete')   // 添加删除按钮
        ->setNID(61)
        ->setTabNav($tab_list, $status)  // 设置页面Tab导航
        ->addTableColumn('id', 'ID')
        ->addTableColumn('name', '配置名称')
        ->addTableColumn('title', '标题')
        ->addTableColumn('group', '分组')
        ->addTableColumn('type', '类型')
        ->addTableColumn('right_button', '操作', 'btn')
        ->setTableDataList($data_list)     // 数据列表
        ->setTableDataPage($page->show())  // 数据列表分页
        ->addRightButton('edit')           // 添加编辑按钮
        ->addRightButton('forbid')         // 添加禁用/启用按钮
        ->addRightButton('delete')         // 添加删除按钮
        ->build();
    }
    
    public function forbid(){
        
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要禁用的数据');
        }
        $r = parent::_forbid($ids);
        if($r !== false){
            sysLogs('配置项id: ' . $ids . ' 禁用');
            S('DB_CONFIG_DATA', null);
            $this->success('禁用成功', U(CONTROLLER_NAME . '/index'));
        }
        else{
            $this->error($this->_getError());
        }
    }
    
    public function resume(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要启用的数据');
        }
        $r = parent::_resume($ids);
        if($r !== false){
            sysLogs('配置项id: ' . $ids . ' 启用');
            S('DB_CONFIG_DATA', null);
            $this->success('启用成功', U(CONTROLLER_NAME . '/index'));
        }
        else{
            $this->error($this->_getError());
        }
        
    }
    
    public function delete(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要删除的数据');
        }
        $r = parent::_del($ids);
        if($r === false){
            $this->error($this->_getError());
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
        
            $config_model = D('Config');
            $r = $config_model->createAdd($data);
            if($r === false){
                $this->error($config_model->getError());
            }
            else{
                sysLogs('新增配置项ID:' . $r);
                S('DB_CONFIG_DATA', null);
                $this->success('新增成功', U(CONTROLLER_NAME . '/index'));
            }
        }
        else {
            // 使用FormBuilder快速建立表单页面。
            $type_list = C('FORM_ITEM_TYPE');
            foreach($type_list as $key => $type){
                $type_options[$key] = $type[0];
            }
            
            $group_options = C('CONFIG_GROUP_LIST');
            
            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('新增配置') //设置页面标题
                    ->setNID(61)
                    ->setPostUrl(U('add'))    //设置表单提交地址
                    ->addFormItem('name', 'text', '配置名称', '用于C函数调用，只能使用英文+下划线且不能重复')
                    ->addFormItem('title', 'text', '配置标题', '用于后台显示的配置标题')
                    ->addFormItem('sort', 'text', '排序', '显示顺序')
                    ->addFormItem('type', 'select', '配置类型', '系统会根据不同类型解析配置值', $type_options)
                    ->addFormItem('group', 'select', '配置分组', '配置信息的分组|不分组则不会显示该配置项', $group_options)
                    ->addFormItem('value', 'textarea', '配置值')
                    ->addFormItem('extra', 'textarea', '配置项', '只有当配置类型为复选框、下拉框、单选按钮时需要填写')
                    ->addFormItem('remark', 'textarea', 'tips', '配置项说明提示')
                    ->build();
        }
    }
    
    public function edit($id){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');
            $config_model = D('Config');
            $r = $config_model->createSave($data);

            if($r === false){
                $this->error($config_model->getError());
            }
            else{
                sysLogs('修改配置项ID:' . $id);
                S('DB_CONFIG_DATA', null);
                $this->success('修改成功', U('index'));
            }
        } else {
            // 使用FormBuilder快速建立表单页面。
            $type_list = C('FORM_ITEM_TYPE');
            foreach($type_list as $key => $type){
                $type_options[$key] = $type[0];
            }
            
            $group_options = C('CONFIG_GROUP_LIST');
            
            $form_data = D('Config')->getOne($id);
            
            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('编辑配置') //设置页面标题
                    ->setNID(61)
                    ->setPostUrl(U('edit'))    //设置表单提交地址
                    ->addFormItem('id', 'hidden', 'ID')
                    ->addFormItem('name', 'text', '配置名称', '用于C函数调用，只能使用英文+下划线且不能重复')
                    ->addFormItem('title', 'text', '配置标题', '用于后台显示的配置标题')
                    ->addFormItem('sort', 'text', '排序', '显示顺序')
                    ->addFormItem('type', 'select', '配置类型', '系统会根据不同类型解析配置值', $type_options)
                    ->addFormItem('group', 'select', '配置分组', '配置信息的分组|不分组则不会显示该配置项', $group_options)
                    ->addFormItem('value', 'textarea', '配置值')
                    ->addFormItem('extra', 'textarea', '配置项', '只有当配置类型为复选框、下拉框、单选按钮时需要填写')
                    ->addFormItem('remark', 'textarea', 'tips', '配置项说明提示')
                    ->setFormData($form_data)
                    ->build();
        }
    }
    
    public function setting($group = 1){
        $config = new \Common\Model\ConfigModel();
        if(!empty($_POST)){
            $conf_arr = I('config');
            if($conf_arr && is_array($conf_arr)){
                foreach ($conf_arr as $name => $value) {
                    $r = $config->updateConfig($name, $value);
                    if($r === false){
                        $this->error($config->getError());
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
            $data_list   = $config->getConfigList($map);
            
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
