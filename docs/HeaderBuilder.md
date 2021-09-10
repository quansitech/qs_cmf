## HeaderBuilder
修改Header

#### addNavbarRightItem
```text
在header加入一个右侧导航项

有一些用户数据需要提醒管理员处理，例如待审核的注册用户数。
可以使用此方法添加一个右侧导航项，点击该项跳转至处理页面。

参数说明
$navbarRightBuilder NavbarRightBuilder 对象
```

[NavbarRightBuilder使用说明](https://github.com/quansitech/qs_cmf/blob/master/docs/NavbarRightBuilder.md)

```php
$url = U('admin/UserTest/index');
$self = <<<self
<a title="待审核数" href="{$url}">待审核数<span class="number">100</span></a>
self;

$header_builder = new \Qscmf\Builder\HeaderBuilder();
$header_builder
    ->addNavbarRightItem((new \Qscmf\Builder\NavbarRightType\NavbarRightBuilder())
        ->setType('num')
        ->setAttribute(['title' => '待审核数', 'href' => U('UserTest/index')])
        ->setOptions(100))
    ->addNavbarRightItem((new \Qscmf\Builder\NavbarRightType\NavbarRightBuilder())
        ->setType('self')
        ->setOptions($self)
        ->setLiAttribute(['class'=>"dropdown user user-menu"]))
    ->display();
```