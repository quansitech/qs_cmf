<?php

namespace Common\Builder;
use Gy_Library\DBCont;
use Think\Template;
use Think\View;
use Think\Controller;
/**
 * 数据列表自动生成器
 */
class ListBuilder extends Controller {
    private $_meta_title;                  // 页面标题
    private $_top_button_list = array();   // 顶部工具栏按钮组
    private $_search  = array();           // 搜索参数配置
    private $_search_url;                    //搜索按钮指向url
    private $_tab_nav = array();           // 页面Tab导航
    private $_table_column_list = array(); // 表格标题字段
    private $_table_data_list   = array(); // 表格数据列表
    private $_table_data_list_key = 'id';  // 表格数据列表主键字段名
    private $_table_data_page;             // 表格数据分页
    private $_right_button_list = array(); // 表格右侧操作按钮组
    private $_alter_data_list = array();   // 表格数据列表重新修改的项目
    private $_extra_html;                  // 额外功能代码
    private $_template;                    // 模版
    private $_show_check_box = true;
    private $_nid;            //高亮节点ID
    private $_meta_button_list = array();  //标题按钮
    private $_lock_row = 1; //锁定标题
    private $_lock_col = 0; //锁定列

    /**
     * 初始化方法
     * @return $this
     */
    protected function _initialize() {
        if(in_array(strtolower(MODULE_NAME), C('BACKEND_MODULE'))){
            $module_name = 'Admin';
        }
        else{
            $module_name = MODULE_NAME;
        }
        $this->_template = APP_PATH.'Common/Builder/Layout/'.$module_name.'/list.html';
    }

    public function setSearchUrl($url){
        $this->_search_url = $url;
        return $this;
    }

    public function setLockRow($row){
        $this->_lock_row = $row;
        return $this;
    }

    public function setLockCol($col){
        $this->_lock_col = $col;
        return $this;
    }

    public function setNIDByNode($module, $controller, $action){
        $module_ent = D('Node')->where(['name' => $module, 'level' => DBCont::LEVEL_MODULE, 'status' => DBCont::NORMAL_STATUS])->find();

        if(!$module_ent){
            E('setNIDByNode 传递的参数module不存在');
        }

        $controller_ent = D('Node')->where(['name' => $controller, 'level' => DBCont::LEVEL_CONTROLLER, 'status' => DBCont::NORMAL_STATUS, 'pid' => $module_ent['id']])->find();
        if(!$controller_ent){
            E('setNIDByNode 传递的参数controller不存在');
        }

        $action_ent = D('Node')->where(['name' => $action, 'level' => DBCont::LEVEL_ACTION, 'status' => DBCont::NORMAL_STATUS, 'pid' => $controller_ent['id']])->find();
        if(!$action_ent){
            E('setNIDByNode 传递的参数action不存在');
        }
        else{
            return $this->setNID($action_ent['id']);
        }
    }

    public function setNID($nid){
        $this->_nid = $nid;
        return $this;
    }

    public function setCheckBox($flag){
        $this->_show_check_box = $flag;
        return $this;
    }

    /**
     * 设置页面标题
     * @param $title 标题文本
     * @return $this
     */
    public function setMetaTitle($meta_title) {
        $this->_meta_title = $meta_title;
        return $this;
    }

    public function addMetaButton($type, $attribute = null, $tips = ''){
        switch ($type) {
            case 'return':  // 添加新增按钮
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '返回';
                $my_attribute['class'] = 'btn btn-primary';
                $my_attribute['href']  = 'javascript:history.go(-1)';

                /**
                 * 如果定义了属性数组则与默认的进行合并
                 * 用户定义的同名数组元素会覆盖默认的值
                 * 比如$builder->addTopButton('add', array('title' => '换个马甲'))
                 * '换个马甲'这个碧池就会使用山东龙潭寺的十二路谭腿第十一式“风摆荷叶腿”
                 * 把'新增'踢走自己霸占title这个位置，其它的属性同样道理
                 */
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }
                $class = $my_attribute['class'];
                $my_attribute['class'] = 'pull-right ' . $class;
                // 这个按钮定义好了把它丢进按钮池里
                break;
            case 'self': //添加自定义按钮(第一原则使用上面预设的按钮，如果有特殊需求不能满足则使用此自定义按钮方法)
                // 预定义按钮属性以简化使用
                $my_attribute['class'] = 'btn btn-danger';

                // 如果定义了属性数组则与默认的进行合并
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                } else {
                    $my_attribute['title'] = '该自定义按钮未配置属性';
                }
                $class = $my_attribute['class'];
                $my_attribute['class'] = 'pull-right ' . $class;
                // 这个按钮定义好了把它丢进按钮池里
                break;
        }
        if($tips != ''){
            $my_attribute['tips'] = $tips;
        }
        $this->_meta_button_list[] = $my_attribute;
        return $this;
    }

    /**
     * 加入一个列表顶部工具栏按钮
     * 在使用预置的几种按钮时，比如我想改变新增按钮的名称
     * 那么只需要$builder->addTopButton('add', array('title' => '换个马甲'))
     * 如果想改变地址甚至新增一个属性用上面类似的定义方法
     * @param string $type 按钮类型，主要有add/resume/forbid/recycle/restore/delete/self七几种取值
     * @param array  $attr 按钮属性，一个定了标题/链接/CSS类名等的属性描述数组
     * @return $this
     */
    public function addTopButton($type, $attribute = null, $tips = '', $auth_node = '') {
        switch ($type) {
            case 'addnew':  // 添加新增按钮
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '新增';
                $my_attribute['class'] = 'btn btn-primary';
                $my_attribute['href']  = U(MODULE_NAME.'/'.CONTROLLER_NAME.'/add');

                /**
                 * 如果定义了属性数组则与默认的进行合并
                 * 用户定义的同名数组元素会覆盖默认的值
                 * 比如$builder->addTopButton('add', array('title' => '换个马甲'))
                 * '换个马甲'这个碧池就会使用山东龙潭寺的十二路谭腿第十一式“风摆荷叶腿”
                 * 把'新增'踢走自己霸占title这个位置，其它的属性同样道理
                 */
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                break;
            case 'resume':  // 添加启用按钮(禁用的反操作)
                //预定义按钮属性以简化使用
                $my_attribute['title'] = '启用';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-success ajax-post confirm';
                $my_attribute['href']  = U(
                    '/' . MODULE_NAME.'/'.CONTROLLER_NAME.'/resume'
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                break;
            case 'forbid':  // 添加禁用按钮(启用的反操作)
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '禁用';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-warning ajax-post confirm';
                $my_attribute['href']  = U(
                    '/' . MODULE_NAME.'/'.CONTROLLER_NAME.'/forbid'
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                //这个按钮定义好了把它丢进按钮池里
                break;
            case 'export':
                $my_attribute['type'] = 'export';
                $my_attribute['title'] = '导出excel';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-primary export_excel';

                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }
                break;
//            case 'recycle':  // 添加回收按钮(还原的反操作)
//                // 预定义按钮属性以简化使用
//                $my_attribute['title'] = '回收';
//                $my_attribute['target-form'] = 'ids';
//                $my_attribute['class'] = 'btn btn-danger ajax-post confirm';
//                $my_attribute['model'] = $attribute['model'] ? : CONTROLLER_NAME;
//                $my_attribute['href']  = U(
//                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
//                    array(
//                        'status' => 'recycle',
//                        'model' => $my_attribute['model']
//                    )
//                );
//
//                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
//                if ($attribute && is_array($attribute)) {
//                    $my_attribute = array_merge($my_attribute, $attribute);
//                }
//
//                // 这个按钮定义好了把它丢进按钮池里
//                $this->_top_button_list[] = $my_attribute;
//                break;
//            case 'restore':  // 添加还原按钮(回收的反操作)
//                // 预定义按钮属性以简化使用
//                $my_attribute['title'] = '还原';
//                $my_attribute['target-form'] = 'ids';
//                $my_attribute['class'] = 'btn btn-success ajax-post confirm';
//                $my_attribute['model'] = $attribute['model'] ? : CONTROLLER_NAME;
//                $my_attribute['href']  = U(
//                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
//                    array(
//                        'status' => 'restore',
//                        'model' => $my_attribute['model']
//                    )
//                );
//
//                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
//                if ($attribute && is_array($attribute)) {
//                    $my_attribute = array_merge($my_attribute, $attribute);
//                }
//
//                // 这个按钮定义好了把它丢进按钮池里
//                $this->_top_button_list[] = $my_attribute;
//                break;
            case 'delete': // 添加删除按钮(我没有反操作，删除了就没有了，就真的找不回来了)
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '删除';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-danger ajax-post confirm';
                $my_attribute['href']  = U(
                    '/' . MODULE_NAME.'/'.CONTROLLER_NAME.'/delete'
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                break;
            case 'save':
                $my_attribute['title'] = '保存';
                $my_attribute['target-form'] = 'save';
                $my_attribute['class'] = 'btn btn-primary ajax-post confirm';
                $my_attribute['href']  = U(
                    '/' . MODULE_NAME.'/'.CONTROLLER_NAME.'/save'
                );
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }
                break;
            case 'self': //添加自定义按钮(第一原则使用上面预设的按钮，如果有特殊需求不能满足则使用此自定义按钮方法)
                // 预定义按钮属性以简化使用
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-danger';

                // 如果定义了属性数组则与默认的进行合并
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                } else {
                    $my_attribute['title'] = '该自定义按钮未配置属性';
                }
                // 这个按钮定义好了把它丢进按钮池里
                break;
        }
        if($tips != ''){
            $my_attribute['tips'] = $tips;
        }
        if($auth_node != ''){
            $my_attribute['auth_node'] = $auth_node;
        }

        $this->_top_button_list[] = $my_attribute;
        return $this;
    }


    public function addSearchItem($name, $type, $title='', $options = array()){
        $search_item = array(
            'name' => $name,
            'type' => $type,
            'title' => $title,
            'options' => $options
        );

        $this->_search[] = $search_item;
        return $this;
    }



    /**
     * 设置Tab按钮列表
     * @param $tab_list Tab列表  array(
    'title' => '标题',
    'href' => 'http://www.corethink.cn'
    )
     * @param $current_tab 当前tab
     * @return $this
     */
    public function setTabNav($tab_list, $current_tab) {
        $this->_tab_nav = array(
            'tab_list' => $tab_list,
            'current_tab' => $current_tab
        );
        return $this;
    }

    /**
     * 加一个表格标题字段
     */
    public function addTableColumn($name, $title, $type = null, $value = '', $editable = false, $tip = '') {
        $column = array(
            'name' => $name,
            'title' => $title,
            'editable' => $editable,
            'type' => $type,
            'value' => $value,
            'tip' => $tip
        );
        $this->_table_column_list[] = $column;
        return $this;
    }

    /**
     * 表格数据列表
     */
    public function setTableDataList($table_data_list) {
        $this->_table_data_list = $table_data_list;
        return $this;
    }

    /**
     * 表格数据列表的主键名称
     */
    public function setTableDataListKey($table_data_list_key) {
        $this->_table_data_list_key = $table_data_list_key;
        return $this;
    }

    /**
     * 加入一个数据列表右侧按钮
     * 在使用预置的几种按钮时，比如我想改变编辑按钮的名称
     * 那么只需要$builder->addRightpButton('edit', array('title' => '换个马甲'))
     * 如果想改变地址甚至新增一个属性用上面类似的定义方法
     * 因为添加右侧按钮的时候你并没有办法知道数据ID，于是我们采用__data_id__作为约定的标记
     * __data_id__会在display方法里自动替换成数据的真实ID
     * @param string $type 按钮类型，edit/forbid/recycle/restore/delete/self六种取值
     * @param array  $attr 按钮属性，一个定了标题/链接/CSS类名等的属性描述数组
     * @return $this
     */
    public function addRightButton($type, $attribute = null, $tips = '', $auth_node = '') {
        switch ($type) {
            case 'edit':  // 编辑按钮
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '编辑';
                $my_attribute['class'] = 'label label-primary';
                $my_attribute['href']  = U(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/edit',
                    array($this->_table_data_list_key => '__data_id__')
                );

                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里

                break;
            case 'forbid':  // 改变记录状态按钮，会更具数据当前的状态自动选择应该显示启用/禁用
                //预定义按钮属
                $my_attribute['type'] = 'forbid';
                $my_attribute['0']['title'] = '启用';
                $my_attribute['0']['class'] = 'label label-success ajax-get confirm';
                $my_attribute['0']['href']  = U(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/resume',
                    array(
                        'ids' => '__data_id__'
                    )
                );
                $my_attribute['1']['title'] = '禁用';
                $my_attribute['1']['class'] = 'label label-warning ajax-get confirm';
                $my_attribute['1']['href']  = U(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/forbid',
                    array(
                        'ids' => '__data_id__'
                    )
                );

                break;
//            case 'hide':  // 改变记录状态按钮，会更具数据当前的状态自动选择应该显示隐藏/显示
//                // 预定义按钮属
//                $my_attribute['type'] = 'hide';
//                $my_attribute['model'] = $attribute['model'] ? : CONTROLLER_NAME;
//                $my_attribute['2']['title'] = '显示';
//                $my_attribute['2']['class'] = 'label label-success ajax-get confirm';
//                $my_attribute['2']['href']  = U(
//                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
//                    array(
//                        'status' => 'show',
//                        'ids' => '__data_id__',
//                        'model' => $my_attribute['model']
//                    )
//                );
//                $my_attribute['1']['title'] = '隐藏';
//                $my_attribute['1']['class'] = 'label label-info ajax-get confirm';
//                $my_attribute['1']['href']  = U(
//                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
//                    array(
//                        'status' => 'hide',
//                        'ids' => '__data_id__',
//                        'model' => $my_attribute['model']
//                    )
//                );
//
//                // 这个按钮定义好了把它丢进按钮池里
//                $this->_right_button_list[] = $my_attribute;
//                break;
//            case 'recycle':
//                // 预定义按钮属性以简化使用
//                $my_attribute['title'] = '回收';
//                $my_attribute['class'] = 'label label-danger ajax-get confirm';
//                $my_attribute['model'] = $attribute['model'] ? : CONTROLLER_NAME;
//                $my_attribute['href'] = U(
//                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
//                    array(
//                        'status' => 'recycle',
//                        'ids' => '__data_id__',
//                        'model' => $my_attribute['model']
//                    )
//                );
//
//                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的顶部按钮
//                if ($attribute && is_array($attribute)) {
//                    $my_attribute = array_merge($my_attribute, $attribute);
//                }
//
//                // 这个按钮定义好了把它丢进按钮池里
//                $this->_right_button_list[] = $my_attribute;
//                break;
//            case 'restore':
//                // 预定义按钮属性以简化使用
//                $my_attribute['title'] = '还原';
//                $my_attribute['class'] = 'label label-success ajax-get confirm';
//                $my_attribute['model'] = $attribute['model'] ? : CONTROLLER_NAME;
//                $my_attribute['href'] = U(
//                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
//                    array(
//                        'status' => 'restore',
//                        'ids' => '__data_id__',
//                        'model' => $my_attribute['model']
//                    )
//                );
//
//                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的顶部按钮
//                if ($attribute && is_array($attribute)) {
//                    $my_attribute = array_merge($my_attribute, $attribute);
//                }
//
//                // 这个按钮定义好了把它丢进按钮池里
//                $this->_right_button_list[] = $my_attribute;
//                break;
            case 'delete':
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '删除';
                $my_attribute['class'] = 'label label-danger ajax-get confirm';
                $my_attribute['href'] = U(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/delete',
                    array(
                        'ids' => '__data_id__'
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的顶部按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                break;
            case 'self':
                // 预定义按钮属性以简化使用
                $my_attribute['class'] = 'label label-default';

                // 如果定义了属性数组则与默认的进行合并
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                } else {
                    $my_attribute['title'] = '该自定义按钮未配置属性';
                }

            // 这个按钮定义好了把它丢进按钮池里
        }

        if($tips != ''){
            $my_attribute['tips'] = $tips;
        }

        if($auth_node != ''){
            $my_attribute['auth_node'] = $auth_node;
        }

        $this->_right_button_list[] = $my_attribute;
        return $this;
    }

    /**
     * 设置分页
     * @param $page
     * @return $this
     */
    public function setTableDataPage($table_data_page) {
        $this->_table_data_page = $table_data_page;
        return $this;
    }

    /**
     * 修改列表数据
     * 有时候列表数据需要在最终输出前做一次小的修改
     * 比如管理员列表ID为1的超级管理员右侧编辑按钮不显示删除
     * @param $page
     * @return $this
     */
    public function alterTableData($condition, $alter_data) {
        $this->_alter_data_list[] = array(
            'condition' => $condition,
            'alter_data' => $alter_data
        );
        return $this;
    }

    /**
     * 设置额外功能代码
     * @param $extra_html 额外功能代码
     * @return $this
     */
    public function setExtraHtml($extra_html) {
        $this->_extra_html = $extra_html;
        return $this;
    }

    /**
     * 设置页面模版
     * @param $template 模版
     * @return $this
     */
    public function setTemplate($template) {
        $this->_template = $template;
        return $this;
    }

    /**
     * 显示页面
     */
    public function display() {
        // 编译data_list中的值
        foreach ($this->_table_data_list as &$data) {
            // 编译表格右侧按钮
            if ($this->_right_button_list) {
                foreach ($this->_right_button_list as $right_button) {
                    // 禁用按钮与隐藏比较特殊，它需要根据数据当前状态判断是显示禁用还是启用
                    if ($right_button['type'] === 'forbid' || $right_button['type'] === 'hide'){
                        $right_button = $right_button[$data['status']];
                    }

                    if(isset($right_button['{key}']) && isset($right_button['{condition}']) && isset($right_button['{value}'])){
                        $continue_flag = false;
                        switch($right_button['{condition}']){
                            case 'eq':
                                if($data[$right_button['{key}']] != $right_button['{value}']){
                                    $continue_flag = true;
                                }
                                break;
                            case 'neq':
                                if($data[$right_button['{key}']] == $right_button['{value}']){
                                    $continue_flag = true;
                                }
                                break;
                        }
                        if($continue_flag){
                            continue;
                        }
                        unset($right_button['{key}']);
                        unset($right_button['{condition}']);
                        unset($right_button['{value}']);
                    }



                    // 将约定的标记__data_id__替换成真实的数据ID
                    $right_button['href'] = preg_replace(
                        '/__data_id__/i',
                        $data[$this->_table_data_list_key],
                        $right_button['href']
                    );

                    //将data-id的值替换成真实数据ID
                    $right_button['data-id'] = preg_replace(
                        '/__data_id__/i',
                        $data[$this->_table_data_list_key],
                        $right_button['data-id']
                    );

                    $tips = '';
                    if($right_button['tips'] && is_string($right_button)){
                        $tips = '<span class="badge">' . $right_button['tips'] . '</span>';
                    }
                    else if($right_button['tips'] && $right_button['tips'] instanceof \Closure){
                        $tips_value = $right_button['tips']($data[$this->_table_data_list_key]);
                        $tips = '<span class="badge">' . $tips_value . '</span>';
                    }

                    // 编译按钮属性
                    $right_button['attribute'] = $this->compileHtmlAttr($right_button);

                    //是否存在权限控制，存在则检验有无权限值
                    if(!$right_button['auth_node'] || verifyAuthNode($right_button['auth_node'])){
                        $data['right_button'] .= '<a '.$right_button['attribute'] .'>'.$right_button['title']. $tips. '</a> ';
                    }

                    while(preg_match('/__(.+?)__/i', $data['right_button'], $matches)){
                        $data['right_button'] = str_replace('__' . $matches[1] . '__', $data[$matches[1]], $data['right_button']);
                    }
                }
            }

            // 根据表格标题字段指定类型编译列表数据
            foreach ($this->_table_column_list as &$column) {
                switch ($column['type']) {
                    case 'status':
                        switch($data[$column['name']]){
                            case '0':
                                $data[$column['name']] = '<i class="fa fa-ban text-danger"></i>';
                                break;
                            case '1':
                                $data[$column['name']] = '<i class="fa fa-check text-success"></i>';
                                break;
                        }
                        break;
                    case 'icon':
                        $data[$column['name']] = '<i class="'.$data[$column['name']].'"></i>';
                        break;
                    case 'date':
                        $data[$column['name']] = time_format($data[$column['name']], 'Y-m-d');
                        break;
                    case 'time':
                        $data[$column['name']] = time_format($data[$column['name']]);
                        break;
                    case 'picture':
                        $data[$column['name']] = '<img src="'.showFileUrl($data[$column['name']]).'">';
                        break;
                    case 'type':
                        $form_item_type = C('FORM_ITEM_TYPE');
                        $data[$column['name']] = $form_item_type[$data[$column['name']]][0];
                        break;
                    case 'fun':
                        if(preg_match('/(.+)->(.+)\((.+)\)/', $column['value'], $object_matches)){
                            $object_matches[3] = str_replace('\'', '', $object_matches[3]);
                            $object_matches[3] = str_replace('"', '', $object_matches[3]);
                            $object_matches[3] = str_replace('__data_id__', $data[$column['name']], $object_matches[3]);
                            $object_matches[3] = str_replace('__id__', $data[$this->_table_data_list_key], $object_matches[3]);
                            $param_arr = explode(',', $object_matches[3]);
                            if(preg_match('/(.+)\((.+)\)/', $object_matches[1], $object_func_matches)){
                                $object_func_matches[2] = str_replace('\'', '', $object_func_matches[2]);
                                $object_func_matches[2] = str_replace('"', '', $object_func_matches[2]);
                                $object_param_arr = explode(',', $object_func_matches[2]);
                                $object = call_user_func_array($object_func_matches[1], $object_param_arr);
                                $data[$column['name']] = call_user_func_array(array($object, $object_matches[2]), $param_arr);
                            }
                        }
                        else if(preg_match('/(.+)\((.+)\)/', $column['value'], $func_matches)){
                            $func_matches[2] = str_replace('\'', '', $func_matches[2]);
                            $func_matches[2] = str_replace('"', '', $func_matches[2]);
                            $func_matches[2] = str_replace('__data_id__', $data[$column['name']], $func_matches[2]);
                            $func_matches[2] = str_replace('__id__', $data[$this->_table_data_list_key], $func_matches[2]);
                            $func_param_arr = explode(',', $func_matches[2]);
                            $data[$column['name']] = call_user_func_array($func_matches[1], $func_param_arr);
                        }
                        break;
                    case 'a': //A标签 fun对应的就是A标签的属性
                        $a_str = '<a ' . $this->compileHtmlAttr($column['value']) . ' >' . $data[$column['name']] . '</a>';
                        if(preg_match('/__(.+?)__/', $a_str, $matches)){
                            $a_str = str_replace('__' . $matches[1] . '__', $data[$matches[1]], $a_str);
                        }
                        $data[$column['name']] = $a_str;
                        break;
                    case 'self':
                        $html = $column['value'];
                        while(preg_match('/__(.+?)__/i', $html, $matches)){
                            $html = str_replace('__' . $matches[1] . '__', $data[$matches[1]], $html);
                        }
                        $data[$column['name']] = $html;
                        break;
                }
            }

            /**
             * 修改列表数据
             * 有时候列表数据需要在最终输出前做一次小的修改
             * 比如管理员列表ID为1的超级管理员右侧编辑按钮不显示删除
             */
            if ($this->_alter_data_list) {
                foreach ($this->_alter_data_list as $alter) {
                    if ($data[$alter['condition']['key']] === $alter['condition']['value']) {
                        //寻找alter_data里需要替代的变量
                        foreach($alter['alter_data'] as $key => $val){
                            while(preg_match('/.+\{\$(.+)\}.+/i', $val, $matches)){
                                $val = str_replace('{$' . $matches[1] . '}', $data[$matches[1]], $val);
                            }
                            $alter['alter_data'][$key] = $val;
                        }
                        $data = array_merge($data, $alter['alter_data']);
                    }
                }
            }
        }

        //编译top_button_list中的HTML属性
        if ($this->_top_button_list) {
            $top_button_list = [];
            foreach ($this->_top_button_list as $button) {
                if(!$button['auth_node'] || verifyAuthNode($button['auth_node'])) {
                    $this->compileButton($button);
                    $top_button_list[] = $button;
                }
            }
            $this->_top_button_list = $top_button_list;
        }

        if ($this->_meta_button_list) {
            foreach ($this->_meta_button_list as &$button) {
                $this->compileButton($button);
            }
        }
        $this->assign('meta_title',          $this->_meta_title);          // 页面标题
        $this->assign('top_button_list',     $this->_top_button_list);     // 顶部工具栏按钮
        $this->assign('meta_button_list',     $this->_meta_button_list);
        $this->assign('search',              $this->_search);              // 搜索配置
        $this->assign('tab_nav',             $this->_tab_nav);             // 页面Tab导航
        $this->assign('table_column_list',   $this->_table_column_list);   // 表格的列
        $this->assign('table_data_list',     $this->_table_data_list);     // 表格数据
        $this->assign('table_data_list_key', $this->_table_data_list_key); // 表格数据主键字段名称
        $this->assign('pagination',     $this->_table_data_page);     // 表示个数据分页
        $this->assign('right_button_list',   $this->_right_button_list);   // 表格右侧操作按钮
        $this->assign('alter_data_list',     $this->_alter_data_list);     // 表格数据列表重新修改的项目
        $this->assign('extra_html',          $this->_extra_html);          // 额外HTML代码
        $this->assign('show_check_box', $this->_show_check_box);
        $this->assign('nid', $this->_nid);
        $this->assign('lock_row', $this->_lock_row);
        $this->assign('lock_col', $this->_lock_col);
        $this->assign('search_url', $this->_search_url);
        parent::display($this->_template);
    }

    protected function compileButton(&$button){
        $button['attribute'] = $this->compileHtmlAttr($button);
        if($button['tips'] != ''){
            $button['tips'] = '<span class="badge">' . $button['tips'] . '</span>';
        }
    }

    //编译HTML属性
    protected function compileHtmlAttr($attr) {
        $result = array();
        foreach ($attr as $key => $value) {
            if($key == 'tips'){
                continue;
            }

            if(!empty($value)){
                $value = htmlspecialchars($value);
                $result[] = "$key=\"$value\"";
            }
        }
        $result = implode(' ', $result);
        return $result;
    }
}
