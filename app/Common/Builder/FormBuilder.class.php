<?php

namespace Common\Builder;
use Gy_Library\DBCont;
use Think\View;
use Think\Controller;
/**
 * 表单页面自动生成器
 */
class FormBuilder extends Controller {
    private $_meta_title;            // 页面标题
    private $_tab_nav = array();     // 页面Tab导航
    private $_post_url;              // 表单提交地址
    private $_form_items = array();  // 表单项目
    private $_extra_items = array(); // 额外已经构造好的表单项目
    private $_form_data = array();   // 表单数据
    private $_extra_html;            // 额外功能代码
    private $_ajax_submit = true;    // 是否ajax提交
    private $_template;              // 模版
    private $_nid;            //高亮节点ID
    private $_custom_html;
    private $_form_item_Filter = null;

    /**
     * 初始化方法
     * @return $this
     */
    protected function _initialize() {
        $this->_template = APP_PATH.'Common/Builder/Layout/'.MODULE_NAME.'/form.html';
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
    
    public function setCustomHtml($custom_html){
        $this->_custom_html = $custom_html;
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

    /**
     * 设置Tab按钮列表
     * @param $tab_list    Tab列表  array('title' => '标题', 'href' => 'http://www.corethink.cn')
     * @param $current_tab 当前tab
     * @return $this
     */
    public function setTabNav($tab_list, $current_tab) {
        $this->_tab_nav = array('tab_list' => $tab_list, 'current_tab' => $current_tab);
        return $this;
    }

    /**
     * 直接设置表单项数组
     * @param $form_items 表单项数组
     * @return $this
     */
    public function setExtraItems($extra_items) {
        $this->_extra_items = $extra_items;
        return $this;
    }

    /**
     * 设置表单提交地址
     * @param $url 提交地址
     * @return $this
     */
    public function setPostUrl($post_url) {
        $this->_post_url = $post_url;
        return $this;
    }

    public function setFormItemFilter(\Closure $filter){
        $this->_form_item_Filter = $filter;
        return $this;
    }

    /**
     * 加入一个表单项
     * @param $type 表单类型(取值参考系统配置FORM_ITEM_TYPE)
     * @param $title 表单标题
     * @param $tip 表单提示说明
     * @param $name 表单名
     * @param $options 表单options
     * @param $extra_class 表单项是否隐藏
     * @param $extra_attr 表单项额外属性
     * @return $this
     */
    public function addFormItem($name, $type, $title, $tip = '', $options = array(), $extra_class = '', $extra_attr = '') {
        $item['name'] = $name;
        $item['type'] = $type;
        $item['title'] = $title;
        $item['tip'] = $tip;
        $item['options'] = $options;
        $item['extra_class'] = $extra_class;
        $item['extra_attr'] = $extra_attr;
        $this->_form_items[] = $item;
        return $this;
    }

    /**
     * 设置表单表单数据
     * @param $form_data 表单数据
     * @return $this
     */
    public function setFormData($form_data) {
        $this->_form_data = $form_data;
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
     * 设置提交方式
     * @param $title 标题文本
     * @return $this
     */
    public function setAjaxSubmit($ajax_submit = true) {
        $this->_ajax_submit = $ajax_submit;
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
        //额外已经构造好的表单项目与单个组装的的表单项目进行合并
        $this->_form_items = array_merge($this->_form_items, $this->_extra_items);

        //编译表单值
        if ($this->_form_data) {
            foreach ($this->_form_items as &$item) {
                if (isset($this->_form_data[$item['name']])) {
                    $item['value'] = $this->_form_data[$item['name']];
                }
            }
        }

        if($this->_form_item_Filter){
            $this->_form_items = call_user_func($this->_form_item_Filter, $this->_form_data, $this->_form_items);
        }

        $this->assign('custom_html', $this->_custom_html);
        $this->assign('meta_title',  $this->_meta_title);  //页面标题
        $this->assign('tab_nav',     $this->_tab_nav);     //页面Tab导航
        $this->assign('post_url',    $this->_post_url);    //标题提交地址
        $this->assign('form_items',  $this->_form_items);  //表单项目
        $this->assign('ajax_submit', $this->_ajax_submit); //额外HTML代码
        $this->assign('extra_html',  $this->_extra_html);  //是否ajax提交
        $this->assign('form_data', $this->_form_data);
        $this->assign('nid', $this->_nid);

        parent::display($this->_template);
    }
}
