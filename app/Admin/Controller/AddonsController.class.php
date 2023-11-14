<?php

namespace Admin\Controller;
use Gy_Library\GyListController;
use Qscmf\Core\QsRbac;;

class AddonsController extends GyListController{
    
    public function index(){
        $addons_model = D('Addons');
        $list = $addons_model->getAllAddonsList();
        
        
        int_to_string($list, array('status'=>array(-1=>'损坏', 0=>'禁用', 1=>'启用', -2=>'未安装')));
        if($list === false){
            $this->error($addons_model->getError());
        }
        
        $count = $list? count($list) : 1 ;
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        $data_list     =   array_slice($list, $page->firstRow, $page->listRows);
        $builder = new \Qscmf\Builder\ListBuilder();
        $builder->setMetaTitle('插件列表')
                    ->setNID(307)
                    ->setCheckBox(false)
                    ->addTableColumn('title', '名称')
                    ->addTableColumn('name', '标识')
                    ->addTableColumn('description', '描述')
                    ->addTableColumn('status_text', '状态')
                    ->addTableColumn('author', '作者')
                    ->addTableColumn('version', '版本')
                    ->addTableColumn('right_button', '操作', 'btn')
                    ->setTableDataList($data_list)
                    ->setTableDataPage($page->show())  // 数据列表分页
                    ->addRightButton('self', array('title' => '设置', 'href' => U('config', array('id' => '__data_id__')), 'class' => 'label label-primary', '{key}' => 'config', '{condition}' => 'neq', '{value}' => 'null'))
                    ->addRightButton('forbid')
                    ->addRightButton('self', array('title' => '卸载', 'href' => U('uninstall', array('id' => '__data_id__')), 'class' => 'label label-danger'))
                    ->alterTableData(  // 修改列表数据
                        array('key' => 'uninstall', 'value' => 1),
                        array('right_button' => '<a class="label label-warning" href="' . U('/admin/addons/install/addon_name/__name__') . '">安装</a>')
                    )
                    ->build();
    }
    
    public function install($addon_name){
        parent::autoCheckToken();
        $class = get_addon_class($addon_name);
        if (!class_exists($class)) {
            $this->error('插件不存在');
        }
        $addons  = new $class;
        $info = $addons->info;
        $hooks = $addons->hooks;
        
        // 检测信息的正确性
        if (!$info || !$addons->checkInfo()){
            $this->error('插件信息缺失');
        }
        
        $install_flag = $addons->install();
        if (!$install_flag) {
            $this->error('执行插件预安装操作失败'.$addons->getError());
        }
        
        if(is_array($addons->admin_list) && $addons->admin_list !== array()){
            $info['has_adminlist'] = 1;
        }else{
            $info['has_adminlist'] = 0;
        }
        
        $addons_model = D('Addons');
        $r = $addons_model->createAdd($info);
        
        if($r === false){
            $this->error($addons_model->getError());
        }
        
        
        
        $config = array('config'=>json_encode($addons->getConfig()));
        $addons_model->where("name='{$addon_name}'")->save($config);
        
        $this->success('安装成功');
    }
    
    public function uninstall($id){
        $addons_ent = D('Addons')->find($id);
        if(!$addons_ent){
            E('数据不存在');
        }
        
        $class = get_addon_class($addons_ent['name']);
        if(!class_exists($class)){
            E('插件不存在');
        }
        
        $addons = new $class;
        $r = $addons->uninstall();
        if($r === false){
            $this->error($addons->getError());
        }
        
        $r = D('Addons')->where("name='{$addons_ent['name']}'")->delete();
        if($r === false){
            $this->error('卸载插件失败');
        }
        else{
            $this->success('卸载成功');
        }
    }
    
    public function config($id){
        if(IS_POST){
            parent::autoCheckToken();
            
            $config =   I('post.config');
            $flag = D('Addons')->where("id={$id}")->setField('config',json_encode($config));
            if($flag !== false){
                $this->success('保存成功', U('index'));
            }else{
                $this->error('保存失败');
            }
        }
        else{
            $addons_ent = D('Addons')->find($id);
            $addon_class = get_addon_class($addons_ent['name']);
            if(!$addons_ent || !class_exists($addon_class)){
                E('插件不存在');
            }

            $data  =   new $addon_class;
            $addons_ent['addon_path'] = $data->addon_path;
            $addons_ent['custom_config'] = $data->custom_config;
            $db_config = $addons_ent['config'];
            $db_config = json_decode($db_config, true);
            $addons_ent['config'] = include $data->config_file;

            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('设置插件-' . $data->info['title'])
                        ->setNID(307);
            
//            if($addons_ent['custom_config']){
//                $data->displayConfig();
//                return;
//            }
            foreach($addons_ent['config'] as $key => $value){
                if($value['type'] == 'group'){
                    foreach($value['options'] as $k => &$v){
                        foreach($v['options'] as $kk => &$vv){
                            $vv['name'] = 'config[' . $kk . ']';
                            $vv['value'] = $db_config[$kk];
                        }
                    }
                }

                $builder->addFormItem('config[' . $key . ']', $value['type'], $value['title'], $value['remark'], $value['options']);
            }

            foreach($db_config as $ck => $cv){
                $db_config['config[' . $ck . ']'] = $cv;
            }
            $builder->setFormData($db_config)
                        ->setPostUrl(U('config', array('id' => $id)))    // 设置表单提交地址
                        ->build();
        }
    }
        
    
//    public function adminList($addon_name, $controller, $action){
//        
//    }
    
    public function forbid(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要禁用的数据');
        }
        $r = parent::_forbid($ids);
        if($r !== false){
            $map['id'] = array('in', explode(',', $ids));
            $names = M('Addons')->where($map)->getField('name', true);
            syslogs('禁用插件:' . implode(',', $names));
            $this->success('禁用成功');
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
            $map['id'] = array('in', explode(',', $ids));
            $names = M('Addons')->where($map)->getField('name', true);
            syslogs('启用插件:' . implode(',', $names));
            $this->success('启用成功');
        }
        else{
            $this->error($this->_getError());
        }
        
    }
    
    public function execute($_addons = null, $_controller = null, $_action = null){
        if(!QsRbac::AccessDecision('admin', $_controller, $_action)){
            E(l('no_auth'));
        }
        
        if(C('URL_CASE_INSENSITIVE')){
            $_addons        =   ucfirst(parse_name($_addons, 1));
            $_controller    =   parse_name($_controller,1);
        }

        $TMPL_PARSE_STRING = C('TMPL_PARSE_STRING');
        $TMPL_PARSE_STRING['__ADDONROOT__'] = __ROOT__ . "/Addons/{$_addons}";
        C('TMPL_PARSE_STRING', $TMPL_PARSE_STRING);

        if(!empty($_addons) && !empty($_controller) && !empty($_action)){
            $Addons = A("Addons://{$_addons}/{$_controller}")->$_action();
        } else {
            $this->error('没有指定插件名称，控制器或操作！');
        }
    }
}
