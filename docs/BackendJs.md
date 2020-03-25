#### 跳转js执行机制

```php
可通过$this->success('修改成功', 'javascript:js代码') 的方式，让页面提交后执行对应的js代码。
该方法仅对使用ajax-post和ajax-form的页面有效。

使用场景举例：
当翻页后，对列表数据进行编辑操作，编辑成功后希望页面跳回之前的页码。

$this->success('修改成功', 'javascript:location.href=document.referrer;');
```

#### 获取当前列表选中checkbox的值
```blade
var ids = $(".check-all").data('checkedIds');
```

#### select2_ajax
```blade
根据API重置select2组件的值

参数
select_dom：dom select2元素节点
url：string 获取数据的API，需返回数据格式：['total_count' => 12,'data' => [['id' => 1, 'text' => 'text']]]
query：json 筛选API的参数，search为dom的值，pageSize最小值为20，最少会包括的参数有：search=xxx&page=1&pageSize=20,数据格式:{pageSize: "{:C('ADMIN_PER_PAGE_NUM')}"}
```

代码示例
```php
    // API
    public function getLibrary($city){
        $search = I('search');
        $page = I('page');
        $page == '' && $page = 1;
        $pageSize = I('pageSize');
        $pageSize == '' && $pageSize = 20;
        $map['status'] = DBCont::NORMAL_STATUS;
        $map['city'] = $city;
        
        if ($search){
            $map['name'] = ['like', "%{$search}%"];
        }

        $library_name = D('Library')->where($map)->field(['id, name'])->page($page, $pageSize)->select();
        $total_count = D('Library')->getListforCount($map);
        
        $lib_data = [];
        foreach ($library_name as $k => $v){
            $lib_data[$k]['id'] = $v['id'];
            $lib_data[$k]['text'] = $v['name'];
        }
        $data = [
            'total_count' => $total_count,
            'data' => $lib_data,
        ];

        $this->ajaxReturn($data);
    }
            
    // js使用代码    
    var city = $('input[name=city]').val();
    var query = {
        city:city,
        pageSize: "{:C('ADMIN_PER_PAGE_NUM')}"
    };
    
    select2_ajax($('.select-two'), "{:U('getLibrary')}", query);
```

#### setCheckedIds
```blade
将当前列表选中checkbox的值赋值到自定义dom的data-checkedIds中。

参数
$this：dom 当前列表checkbox元素节点
selectIds：array 当前列表选中checkbox的值
valDom：string 存放selectIds转换成字符串的元素节点，默认为:.check-all
```