namespace Admin\Controller;
use Gy_Library\GyListController;
use Gy_Library\DBCont;

//自动生成代码
class {$controller_name}Controller extends GyListController{
    
    public function index(){
        $get_data = I('get.');
        $map = array();
        <volist name="select_search" id="select">
        if(isset($get_data['{$select.name}'])){
            $map['{$select.name}'] = $get_data['{$select.name}'];
        }
        </volist>
        
        <notempty name="like_search" id="like">
        if(isset($get_data['key']) && $get_data['word']){
            $map[$get_data['key']] = array('like', '%' . $get_data['word'] . '%');
        }    
        </notempty>
        $model = D('{$model_name}');
        <php>if($tree != "tree"){</php>
        $count = $model->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        
        $data_list = $model->getListForPage($map, $page->nowPage, $page->listRows, 'sort asc');
        <php>}else{</php>
        $data_list = $model->getList($map, 'sort asc');
        $tree = list_to_tree($data_list);
        $data_list = genSelectByTree($tree);
        foreach($data_list as $k=>$v){
            $title_prefix = str_repeat("&nbsp;", $v['level']*4);
            $title_prefix .= empty($title_prefix) ? '' : "┝ ";
            $v["name"] = $title_prefix . $v["name"];
            $data_list[$k] = $v;
        }
        <php>}</php>


        $builder = new \Qscmf\Builder\ListBuilder();
        
        $builder = $builder->setMetaTitle('{$list_title}管理')
        
        <volist name='select_search' id='select'>
        ->addSearchItem('{$select.name}', 'select', '所有{$select.title}', {:showHtmlContent($select['fun'])})
        </volist>
        <notempty name='like_search'>
        ->addSearchItem('', 'select_text', '搜索内容', {$like_search})
        </notempty>
                
        
        ->addTopButton('addnew')
        ->addTopButton('forbid')
        ->addTopButton('resume')
        ->addTopButton('delete');
        <eq name='save_action.action' value='save'>
        $builder->addTopButton('save', array('title' => '{$save_action.btn_title}'));
        </eq>
        
        $builder->setNID({$nid})
        <volist name='show_columns' id='column'>
            ->addTableColumn('{$column.name}', '{$column.title}', '{$column.type}', '{:showHtmlContent(str_replace("'", '"', $column["fun"]))}', <eq name='column.edit' value='true'>true<else/>false</eq>)
        </volist>
        ->addTableColumn('right_button', '操作', 'btn')
        ->setTableDataList($data_list)     
        <php>if($tree != 'tree'){</php>
        ->setTableDataPage($page->show())
        <php>}</php>
        ->addRightButton('edit')         
        ->addRightButton('forbid')      
        ->addRightButton('delete')        
        ->display();
    }
    
    <eq name='save_action.action' value='save'>
    public function save(){
        if(IS_POST){
            $data = I('post.');
            foreach($data['id'] as $k=>$v){
                <volist name='show_columns' id='column'>
                    <eq name='column.edit' value='true'>
                $save_data['{$column.name}'] = $data['{$column.name}'][$k];
                    </eq>
                </volist>
                D('{$model_name}')->where('id=' . $v)->save($save_data);
            }
            $this->success('保存成功', U('index'));
        }
    }
    </eq>

    
    public function add(){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            $model = D('{$model_name}');
            $r = $model->createAdd($data);
            if($r === false){
                $this->error($model->getError());
            }
            else{
                sysLogs('新增{$list_title}id:' . $r);

                $this->success(l('add') . l('success'), U(CONTROLLER_NAME . '/index'));
            }
        }
        else {
            $builder = new \Qscmf\Builder\FormBuilder();
            
            $data_list = array(
            <volist name="add_columns" id="column">
                <notempty name="column.default">
                    "{$column.name}"=>{$column.default},
                </notempty>
            </volist>
            );
            
            if($data_list){
                $builder->setFormData($data_list);
            }
            
            $builder->setMetaTitle('新增{$list_title}') 
                    ->setNID({$nid})
                    ->setPostUrl(U('add'))    
                    <volist name='add_columns' id='column'>
                        ->addFormItem('{$column.name}', '{$column.type}', '{$column.title}','{$column.tips}', <empty name='column.options_fun'>''<else/>{:showHtmlContent($column["options_fun"])}</empty><notempty name="column.extra_attr">,'',"{:showHtmlContent(str_replace('"', "'", $column["extra_attr"]))}"</notempty>)
                    </volist>
                    ->display();
        }
    }
    
    public function edit($id){
        if (IS_POST) {
            parent::autoCheckToken();
            $m_id = I('post.id');
            $data = I('post.');
            $model = D('{$model_name}');
            if(!$m_id){
                E('缺少{$list_title}ID');
            }
            
            $ent = $model->getOne($m_id);
            if(!$ent){
                E('不存在{$list_title}');
            }
            
            <volist name='add_columns' id='column'>
                $ent['{$column.name}'] = $data['{$column.name}'];
            </volist>

            if($model->createSave($ent) === false){
                $this->error($model->getError());
            }
            else{
                sysLogs('修改{$list_title}id:' . $m_id);
                $this->success('修改成功', U('index'));
            }
        } else {

            $info = D('{$model_name}')->getOne($id);


            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('编辑{$list_title}') 
                    ->setPostUrl(U('edit'))    
                    ->setNID({$nid})
                    ->addFormItem('id', 'hidden', 'ID')
                    <volist name='add_columns' id='column'>
                    ->addFormItem('{$column.name}', '{$column.type}', '{$column.title}', '{$column.tips}', <empty name='column.options_fun'>''<else/>{:showHtmlContent($column["options_fun"])}</empty><notempty name="column.extra_attr">,'',"{:showHtmlContent(str_replace('"', "'", $column["extra_attr"]))}"</notempty>)
                    </volist>
                    ->setFormData($info)
                    ->display();
        }
    }
    
    public function forbid(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要禁用的数据');
        }
        $r = parent::_forbid($ids);
        if($r !== false){
            sysLogs('{$list_title}id: ' . $ids . ' 禁用');
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
            sysLogs('{$list_title}id: ' . $ids . ' 启用');
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
            sysLogs('{$list_title}id: ' . $ids . ' 删除');
            $this->success('删除成功', U(MODULE_NAME . '/' . CONTROLLER_NAME . '/index'));
        }
    }

}