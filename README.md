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
### 侧边栏菜单设置及显示
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
方法二：数据库生成迁移和运行迁移，具体用法请自行参照lavaver手册

### 列表生成器
列表生成器：在后台把与数据库交互的数据生成显示。
```
  $builder = new ListBuilder();
        $builder = $builder->setMetaTitle('')
            ->setNIDByNode(MODULE_NAME,CONTROLLER_NAME,'index')
            ->addTopButton('addnew')    // 添加新增按钮;
            ->addTableColumn('name', 'title', '', '', false,'')
            ->addTableColumn('right_button', '操作', 'btn')
            ->setTableDataList($data_list)
            ->addSearchItem('status', 'select', '所有状态', DBCont::getStatusList())
            ->setTabNav($tab_list, $current_tab)  // 设置页面Tab导航
            ->addRightButton('edit')
            ->setTableDataPage($page->show())
            ->display();
```
使用说明：
实例化一个ListBuilder的类，
1）setMetaTitle参数说明：设置列表标题。
2）setNIDByNode，参数说明：选择对应ID后高亮，MODULE_NAME与CONTROLLER_NAME为该节点对应模块和控制器的名字，'index'为该节点名称。
3）addTopButton($type, $attribute = null, $tips = '', $auth_node = '')
参数说明：
	addTopButton增加列表顶部按钮。
	1、$type为增加按钮的类型，主要有addnew(新增)/resume(启用)/forbid(禁用)/save(保存)/delete(删除)/self(自定义)六种取值
 ``` 
          	->addTopButton('addnew')   
            ->addTopButton('resume')
            ->addTopButton('forbid')
            ->addTopButton('save')
            ->addTopButton('delete')
            ->addTopButton('self')
``` 
示例代码实现效果：
<img src="https://github.com/ericlwd/img/blob/master/%E6%96%B0%E5%A2%9E%E6%8C%89%E9%92%AE%E7%B1%BB%E5%9E%8B.png" /><br/>
	2、$attribute为一个定了标题/链接/CSS类名等的属性描述数组，示例代码：
```
$builder->addTopButton('self', array('title' => '管理信息公开分类', 'class' => 'btn btn-success', 'href' => U('InformationCate/index')));
```
上述代码通过数组描述title值为管理信息公开分类，样式为btn btn-success，href指向U函数跳转InformationCate控制器下action为index方法的页面。
示例代码实现效果：
<img src="https://github.com/ericlwd/img/blob/master/SELF%E6%8C%89%E9%92%AE%E7%94%A8%E6%B3%95%E7%A4%BA%E4%BE%8B.png" /><br/>
   3、$tips显示的提示

3）addTableColumn($name, $title, $type = null, $value = '', $editable = false, $tip = '')
参数说明：
  addTableColumn增加一个表格标题字段
  1、$name为实例化对象要选择显示的字段名
  2、$title标题名
  3、$editable能否编辑
  4、$tip显示的提示
 $name、$title、$editable以及$tip示例代码
```
			->addTableColumn('name', '数据名称', '', '', false,'真实的数量')
            ->addTableColumn('number', '数量', '', '', false)
            ->addTableColumn('unit', '数据单位', '', '', false)
            ->addTableColumn('sort', '排序', '', '', true)
            ->addTableColumn('status', '状态', 'status', '', false)
```
 $name、$title、$editable以及$tip示例代码实现效果：
<img src="https://github.com/ericlwd/img/blob/master/tips_title_editable.png" /><br/>
  5、$type的值类型：
  status的状态类型显示（1为启用状态，0为禁用状态），
  btn增加按钮的显示
  fun匹配当前数据的真实ID予以显示
  $type的示例代码：
```
            ->addTableColumn('status', '状态', 'status', '', false)   //增加显示status的状态类型显示
            ->addTableColumn('right_button', '操作', 'btn')   //增加右侧按钮的显示
            ->addTableColumn('publish_date', '发布时间', 'fun', 'date("Y-m-d",__data_id__)', false)   //fun匹配当前数据的真实ID显示对应的发布时间         
```  
 $type的示例代码实现效果：
<img src="https://github.com/ericlwd/img/blob/master/status_btn_fun.png" /><br/>

4）addRightButton($type, $attribute = null, $tips = '', $auth_node = '')
参数说明：addRightButton增加列表右侧按钮，$type类型，$tips显示的提示。
```
            ->addRightButton('edit')                          //增加显示右侧按钮，编辑功能
            ->addRightButton('forbid') 						  //增加显示右侧按钮，启用/禁用功能
            ->addRightButton('delete')  					  //增加显示右侧按钮，删除功能
            ->addTopButton('self')                            //增加显示右侧按钮，自定义功能
``` 

5)setTableDataList($sql)
参数说明：setTableDataList使用表单的数据，$sql为实例化后传进的变量名。

6）setTabNav($tab_list, $current_tab)
参数说明：设置页面Tab导航， $current_tab为当前TAB，$tab_list是Tab列表。
7）setTableDataPage
参数说明：setTableDataPage设置表单数据的分页。
  setTableDataPage示例代码：
```
        $Index_config = D('IndexConfig');         				//实例化对象
        $count = $Index_config->getListForCount($map); 			//获取当前对象的列数
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false); 		//每页显示的数量，ADMIN_PER_PAGE_NUM设定为20
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        $data_list = $Index_config->getListForPage($map, $page->nowPage, $page->listRows, 'id desc');    //实例化对象获取当前页，总页数，id正序排列赋值$data_list

         $builder = new ListBuilder();

         $builder->setTableDataList($data_list)             //显示实例化对象IndexConfig的列表
         		 ->setTableDataPage($page->show()) 			//分页显示列表
         		 ->display();                               //输出
```
8)addSearchItem($name, $type, $title='', $options = array())
参数说明：addSearchItem增加搜索栏，
  1、$name为搜索的字段，
  2、$type为搜索栏的类型，$type可用的值：select可选择对应字段的值（如status字段的启用和禁用）
  3、$title为搜索栏的标题，
  4、$options是一个数组，数组内的值为搜索字段可选择的值。
 ```
           addSearchItem('status', 'select', '所有状态', DBCont::getStatusList())
           addSearchItem('name', 'select_text', '搜索轮播图', array('name'=>'标题'))
 ```
 示例图：
 <img src="https://github.com/ericlwd/img/blob/master/%E6%90%9C%E7%B4%A2%E6%A1%86.png" /><br/>
 示例效果：
<img src="https://github.com/ericlwd/img/blob/master/%E6%90%9C%E7%B4%A2%E6%95%88%E6%9E%9C.png" /><br/>
### 后台表单生成器  
后台表单生成器 :新增数据或编辑后台原有数据所使用。
使用说明：
实例化一个FormBuilder的类，FormBuilder的示例代码：
```      
  $builder = new FormBuilder();
            $builder->setMetaTitle('新增分类')
                ->setNIDByNode(MODULE_NAME,CONTROLLER_NAME,'index')
                ->setPostUrl(U('add'))
                ->addFormItem('title', 'text', '标题', '', '')
                ->addFormItem('summary', 'ueditor', '概述', '', '')
                ->addFormItem('status', 'radio', '状态', '', DBCont::getStatusList())
                ->display();
```
 参数说明:
1)addFormItem($name, $type, $title, $tip = '', $options = array(), $extra_class = '', $extra_attr = '')
addFormItem加入一个表单项。$name为增加的字段名；$type为表单类型；其中表单类型有$title为表单标题；$extra_class表单项是否隐藏；$tip表单提示说明，$extra_attr为表单额外属性；$options是一个数组，数组内的值为表单可选择的值。

type的可选值有hidden(隐藏);num(数字)数字;text(单行文本);textarea(多行文本);password(密码);radio(单选按钮);checkbox (复选框);select(下拉框);date(日期);datetime(时间);picture(单张图片);pictures(多张图片),file(单个文件),files (多个文件),ueditor(百度编辑器 )

示例代码1：
```
			    ->addFormItem('id', 'hidden', '主键ID')    
                ->addFormItem('real_name', 'text', '真实姓名')
                ->addFormItem('nick_name', 'text', '别名')
                ->addFormItem('password', 'password', '密码')
                ->addFormItem('telephone', 'num', '手机号码')
                ->addFormItem('content', 'ueditor', '内容')
                ->addFormItem('edu_type', 'checkbox', '教育类型', '',DBCont::getEduTypeList())
                ->addFormItem('sort', 'text', '排序')
                ->addFormItem('up', 'radio', '置顶', '', DBCont::getBoolStatusList())
                ->addFormItem('status', 'select', '状态', '', DBCont::getStatusList())
```
示例效果1：
<img src="https://github.com/ericlwd/img/blob/master/formbuilder_type1.png" /><br/>
示例代码2：
```
->addFormItem('pic', 'picture_intercept', '封面','', '')
                ->addFormItem('img', 'picture', '二维码图片','', '')
                ->addFormItem('publish_date', 'date', '发布日期')
                ->addFormItem('create_date', 'datetime', '创建日期')
                ->addFormItem('pdf', 'file', '文档')
                ->addFormItem('pdf1', 'files', '多个文档')
                ->addFormItem('imgs', 'pictures', '多张图片','', '')
```
示例效果2：
<img src="https://github.com/ericlwd/img/blob/master/formbuilder_type1.png" /><br/>
<img src="https://github.com/ericlwd/img/blob/master/%E5%A4%9A%E5%BC%A0%E5%9B%BE%E7%89%87%E5%A4%9A%E4%B8%AA%E6%96%87%E4%BB%B6%E7%9A%84%E6%95%88%E6%9E%9C.png" /><br/>

2)setMetaTitle设置列表标题 。     
3)setNIDByNode，选择对应ID后高亮，MODULE_NAME与CONTROLLER_NAME为该节点对应模块和控制器的名字，'index'为该节点名称。
4)setPostUrl设置表单提交地址。

## lincense
[MIT License](https://github.com/tiderjian/lara-for-tp/blob/master/LICENSE.MIT) AND [996ICU License](https://github.com/tiderjian/lara-for-tp/blob/master/LICENSE.996ICU)
