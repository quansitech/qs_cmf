### 介绍
Excel文件生成及Excel文件读取

### 用法

#### ListBuilder
```php
$options  = [
    'row_count' => 500,    //excel生成的行数
    'headers' => [
        [
            'title' => '读者编号',
        ],
        [
            'title' => '姓名',
        ],
        [
            'title' => '性别',
            'type' => 'list',
            'data_source' => '女,男'
        ],
        [
            'title' => '读者类型',
            'type' => 'list',
            'data_source' => '高年级学生,低年级学生,老师'
        ],
        [
            'title' => '读者状态',
            'type' => 'list',
            'data_source' => '停用,正常,注销,挂失'
        ],
        [
            'title' => '注册日期'
        ],
        [
            'title' => '学号'
        ]
    ]
];

//列表数据
//次序与类型必须与options对应
$data = [
    'DD123456',
    '张三',
    '女',
    '高年级学生',
    '停用',
    '2016-07-10',
    'G44532220060903492X'
];
$excel = new \Qscmf\Lib\QsExcel\Excel();
$excel->setBuild(new \Qscmf\Lib\QsExcel\Builder\ListBuilder($options, $data));
$excel->output('reader_import.xlsx');
```

####  ListLoader
```php
$excel = new \Qscmf\Lib\QsExcel\Excel();
$excel->setLoader(new \Qscmf\Lib\QsExcel\Loader\ListLoader());
$list = $excel->load($file);  //file excel文件绝对路径    list 返回excel包括header在内的所有列表数据
```
