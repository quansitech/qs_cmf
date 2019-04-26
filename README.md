# qscmf

![lincense](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)
[![LICENSE](https://img.shields.io/badge/license-Anti%20996-blue.svg)](https://github.com/996icu/996.ICU/blob/master/LICENSE)
![Pull request welcome](https://img.shields.io/badge/pr-welcome-green.svg?style=flat-square)

## 介绍
快速搭建信息管理类系统的框架。基于tp3.2开发，但修改了部分tp源码 ，如该框架支持嵌套事务（原tp3.2不支持嵌套事务）。除此外还集成了众多信息管理系统用到的功能，如队列、插件系统、后台表单生成器、列表生成器等。

## 截图
<img src="https://user-images.githubusercontent.com/1665649/55472251-36458e80-563e-11e9-87c0-10c386d5bd78.png" />

## 安装
```
git clone https://github.com/tiderjian/qs_cmf.git
```

composer安装
```
composer require tiderjian/lara-for-tp
```

安装完成后将tiderjian/lara-for-tp下的stub所有文件复制到qs_cmf项目的根目录，并将qs_cmf\migrations复制到lara\database\migrations，配置lara\.env的数据库设置，运行
```
php artisan migrate
```

将web服务器搭起来后，后台登录地址  协议://域名:端口/admin， 账号:admin 密码:admin123


## Elasticsearch
框架为集成Elasticsearch提供了方便的方法, 假设使用者已经具备elasticsearch使用的相关知识。


1. 添加 "elasticsearch/elasticsearch": "~6.0" 到composer.json文件，执行composer update 命令安装扩展包。
2. 安装elasticsearch, 具体安装方法自行查找，推荐使用laradock作为开发环境，直接集成了elasticsearch的docker安装环境。
3. 安装ik插件，安装查找elasticsearch官方文档。
4. 修改app/Common/Conf/config.php 里的 ELASTICSEARCH_HOSTS值，设置为elasticsearch的启动ip和端口，如laradock的默认设置为10.0.75.1:9200
5. 设置需要使用elastic的model和字段

    以ChapterModel添加title和summary到全文索引为例
    ```blade
    // ChapterModel类必须继承接口
    class ChapterModel  extends \Gy_Library\GyListModel implements \Common\Lib\ElasticsearchModelContract{
 
       // ElasticsearchHelper已经实现了一些帮助函数
       use \Common\Lib\ElasticsearchHelper;
        
       // 初始化全文索引时需要指定该Model要添加的索引记录
       public function elasticsearchIndexList()
       {
            // 如这里chapter表与course表关联，只有当course及chapter状态都为可用，且非描述类chapter(pid = 0为描述类chapter)才会添加全文索引
           return $this->alias('ch')->join('__COURSE__ c ON c.id=ch.course_id and c.status = ' . DBCont::NORMAL_STATUS)->where(['ch.status' => DBCont::NORMAL_STATUS, 'ch.pid' => ['neq', 0]])->field('ch.*')->select();
       }
       
       // chapter进行增删查改时同时会更新索引内容，该方法是指定什么状态的记录才会进行索引更改
       // 返回false为无需索引的记录，true则会进行索引更新
       public function isElasticsearchIndex($ent){
           if($ent['status'] != DBCont::NORMAL_STATUS || $ent['pid'] == 0){
               return false;
           }
   
           $course_ent = D('Course')->find($ent['course_id']);
           if($course_ent['status'] == DBCont::NORMAL_STATUS){
               return true;
           }
           else{
               return false;
           }
       }
   
       // 程序会自动生成索引的配置参数，此处是定义生成参数的规则
       // 以:开头的字母表示该处会自动替换成相应字段的实际值
       // {}表示里面的字符会与替换后的:字段值进行连接，如:id{_chapter}, id实际值为 12，则该处会替换成 12_chapter
       // | 表示可以将字段的实际值传递给指定的函数进行处理，转换成想要的值。如，description字段是富文本内容，我们将html标签进行索引，可以在model方法里自定义一个叫deleteHtmlTag的方法进行处理，当然也可以定义为全局函数，程序会先查找全局是否存在该函数，如果没有再去对象里查找有无该方法
      // index和type的值在建立初始化全文索引时指定，具体查看全文索引初始化说明
       public function elasticsearchAddDataParams()
       {
           return [
               'index' => 'global_search',
               'type' => 'content',
               'id' => ':id{_chapter}',
               'body' => [
                   'title' => ':title',
                   'desc' => ':description|html_entity_decode'
               ]
           ];
       }
    }
    ```
6. 初始化全文索引

    打开Home/Controller/ElasticController.class.php文件, 修改index方法里的$params变量，根据你的需要来设置
    
7. 执行索引初始化，程序会自动检索数据库全部数据表，为需要添加索引的表和字段进行索引添加操作。
    ```blade
    //进入app目录，下面有个makeIndex.php文件
    php makeIndex.php
    ```

## 联动删除
在进行一些表删除操作时，很可能要删除另外几张表的特定数据。联动删除功能只需在Model里定义好联动删除规则，在删除数据时即可自动完成另外多张表的删除操作，可大大简化开发的复杂度。

使用样例
```
//假设有一张文章表Post, 评论表 Message, 点赞表 Like。 
//点赞表有字段type, type_id, 当type=4时，type_id指向文章表的主ID
//评论表有字段post_id，post_id与文章表的主id关联
//这时我们可以在Model里定义 _initialize方法，在该方法内定义 _delete_auto 规则
protected function _initialize() {
    $this->_delete_auto = [
         //实现Message表的联动删除
        ['delete', 'Message', ['id' => 'post_id']],
        //实现点赞表的联动删除，$ent参数就是存放Post表的记录的实体变量
        ['delete', function($ent){
            D('Like')->where(['type' => 4, 'type_id' => $ent['id']])->delete();
        }],
    ];
}
```

目前联动删除的定义规则暂时只有两种，第二种规则比第一种规则更灵活，可应用于更多复杂的场景。第一种规则仅能应用在两个表能通过一个外键表达关联的场景。第一种规则在性能上比第二种更优。

## 文档

## 后台表单生成器、列表生成器功能
1.选择左侧菜单栏→系统设置→菜单列表→新增。如下图所示:
<img src="https://raw.githubusercontent.com/ericlwd/img/master/1.png" /><br/>
2.添加新增菜单的标题，排序（从0开始），自定义icon，类型：backend_menu ,url(非必填)，父菜单：平台，绑定模块以及状态。<br/>
3.添加菜单列表成功后，点击节点列表，点击新增使用新增节点功能。<br/>
4.新增节点，需注意：
1）节点名称为定义的action方法
2）控制器为实现action方法对应的业务逻辑控制器<br/>
举例为Sample控制器下的index方法，如下图所示：
<img src="https://raw.githubusercontent.com/ericlwd/img/master/2.png" /><br/>
5.新增成功后显示<br/>
<img src="https://raw.githubusercontent.com/ericlwd/img/master/3.png" /><br/>
6. 创建数据
方法一：点击新增按钮创建数据
方法二：数据库生成迁移和运行迁移
数据库生成迁移和运行迁移参考文档:
![laravel相关文档]https://learnku.com/docs/laravel/5.8/migrations/3928<br/>
7. 设置需要使用后台表单以及列表controller<br/>
以SampleController.class.php为例
```
<?php
namespace Admin\Controller;
use Common\Builder\FormBuilder;
use Common\Builder\ListBuilder;
use Gy_Library\GyListController;
use Gy_Library\DBCont;


class SampleController extends GyListController
{

    /**     *后台表单以及列表的输出     */

    public function index()
    {
        $builder = new ListBuilder();
        $Sample_data_list = D('Sample')->select();//实例化Sample数据表
        $builder = $builder->setMetaTitle('内容管理')
            ->setNID(992)
            ->addTopButton('save', array('title' => '保存标题'))//增加保存标题的按钮
            ->addTableColumn('id', '主键', '', '', false, '')//增加id栏页面不能编辑
            ->addTableColumn('title', '标题', '', '', true) //增加标题栏页面可以编辑
            ->addTableColumn('status', '状态', 'status', '', false)
            ->addTableColumn('right_button', '操作', 'btn') //增加右键按钮栏的显示
           ->addTableColumn('summary', '概述', '', '', true)
            ->setTableDataList($Sample_data_list)//
            ->addTopButton('addnew') //增加顶部新增按钮
            ->addRightButton('edit') //增加右侧编辑按钮
            ->addRightButton('forbid') //增加禁用编辑按钮
            ->addRightButton('delete') //增加删除编辑按钮
            ->display();

    }

/**     *新增功能的实现     */

    public function add()
    {
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');
            $model = D('sample');
            $r = $model->createAdd($data);
            if ($r === false) {
                $this->error($model->getError());
            } else {
                $this->success('添加成功', U('index'));
            }
        } else {
            $builder = new FormBuilder();
            $builder->setMetaTitle('新增分类')
                ->setNID(992)
                ->setPostUrl(U('add'))
                ->addFormItem('title', 'text', '标题', '', '')
                ->addFormItem('summary', 'ueditor', '概述', '', '')
                ->addFormItem('status', 'radio', '状态', '', DBCont::getStatusList())
                ->display();
        }
    }

/**     *保存标题功能的实现     */

    public function save()
    {
        if (IS_POST) {
            $data = I('post.');
            foreach ($data['id'] as $k => $v) {
                $save_data['title'] = $data['title'][$k];
                $save_data['summary'] = $data['summary'][$k];
                D('sample')->where('id=' . $v)->save($save_data);
            }
            $this->success('保存成功', U('index'));
        }
    }
/**     *编辑功能的实现     */

    public function edit($id)
    {
        if (IS_POST) {
            parent::autoCheckToken();
            $m_id = I('post.id');
            $data = I('post.');
            $model = D('Post');
            if (!$m_id) {
                E('缺少内容ID');
            }

            $ent = $model->getOne($m_id);
            if (!$ent) {
                E('不存在内容');
            }

            $ent['up'] = $data['up'];
            $ent['title'] = $data['title'];
            $ent['cate_id'] = $data['cate_id'];
            $ent['summary'] = $data['summary'];
            $ent['cover_id'] = $data['cover_id'];
            $ent['sort'] = $data['sort'];
            $ent['english_name'] = $data['english_name'];
            $ent['publish_date'] = $data['publish_date'];
            $ent['author'] = $data['author'];
            $ent['url'] = $data['url'];
            $ent['content'] = $data['content'];
            $ent['video'] = $data['video'];
            $ent['images'] = $data['images'];
            $ent['attach'] = $data['attach'];
            $ent['status'] = $data['status'];
            if ($model->createSave($ent) === false) {
                $this->error($model->getError());
            } else {
                sysLogs('修改内容id:' . $m_id);
                $this->success('修改成功', U('index'));
            }
        } else {

            $sample_ent = D('sample')->getOne($id);
            $builder = new FormBuilder();
            $builder->setMetaTitle('编辑内容')
                ->setNID(992)
                ->setPostUrl(U('edit'))
                ->addFormItem('id', 'hidden', '')
                ->addFormItem('title', 'text', '标题','sample formbuilder演示')
                ->addFormItem('summary', 'ueditor', '简介')
                ->display();
        }
}
/**     *禁用功能的实现     */

    public function forbid(){
        $ids = I('ids');
     var_dump($ids);
        if(!$ids){
            $this->error('请选择要禁用的数据');
        }
        $r = parent::_forbid($ids);
        if($r !== false){
            sysLogs(' sample id: ' . $ids . ' 禁用');
            $this->success('禁用成功', U(CONTROLLER_NAME . '/index'));
        }
        else{
            $this->error($this->_getError());
        }
    }
/**     *启用功能的实现     */

    public function resume(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要启用的数据');
        }
        $r = parent::_resume($ids);
        if($r !== false){
            sysLogs('内容id: ' . $ids . ' 启用');
            $this->success('启用成功', U(CONTROLLER_NAME . '/index'));
        }
        else{
            $this->error($this->_getError());
        }

}
/**     *删除功能的实现     */

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
            sysLogs('内容id: ' . $ids . ' 删除');
            $this->success('删除成功', U(MODULE_NAME . '/' . CONTROLLER_NAME . '/index'));
        }
    }
}
?>
```

## lincense
[MIT License](https://github.com/tiderjian/lara-for-tp/blob/master/LICENSE.MIT) AND [996ICU License](https://github.com/tiderjian/lara-for-tp/blob/master/LICENSE.996ICU)
