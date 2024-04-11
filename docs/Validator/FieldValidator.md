# FieldValidator
字段规则



#### addRule

添加字段验证规则

```php
// 参数 array|string  $rule
```

+ 普通字段

  + 当为 string 时，只对参数类型是 boolean 的规则有效，且默认为开启

    ```php
    'required' // 验证规则为 required ，使用默认提示
    ```

  + 当为 array 时，参数说明

    格式为  **['验证规则', '规则参数','错误提示']**
    
    | 名称     | 说明                                                      |
    | -------- | --------------------------------------------------------- |
    | 验证规则 | 必填                                                      |
    | 规则参数 | 非必填,仅 boolean 类型非必填，有值时 boolean 需默认为true |
    | 错误提示 | 非必填，为空则为默认提示                                  |
    
    ```php
    ['required'] // 验证规则为 required ，使用默认提示
    ['required', true,'必填'] // 验证规则为 required ，修改错误提示
    ['min_length', 2] // 验证规则为 min_length ，使用默认提示
    ['range_length', [2,3], '超出范围'] // 验证规则为 range_length ，修改错误提示
    ```
    

  

+ 子表格的设置必须是 **array**

  格式为  **['type' => 'sub_table', 'rules' => '同普通字段']**

  | 名称  | 说明                       |
  | ----- | -------------------------- |
  | type  | 子表格标识，恒为 sub_table |
  | rules | 同普通字段                 |
  
  ```php
  'type' => 'sub_table', // 添加子表格标识
  'rules' => [
      $sub1."_num" => [['required', true,'必填'],'number',['minlength', 2], ['maxlength', 5]],
      $sub1."_date" => [['required'], 'date']
  ]
  ```
  
  



#### 验证规则说明

| 名称         | 参数类型            | 错误提示                             |
| ------------ | ------------------- | ------------------------------------ |
| required     | boolean，默认为true | 此字段必填                           |
| email        | boolean，默认为true | 请输入有效的电子邮件地址             |
| url          | boolean，默认为true | 请输入有效的网址                     |
| date         | boolean，默认为true | 请输入有效的日期 (YYYY-MM-DD)        |
| number       | boolean，默认为true | 请输入有效的数字                     |
| digits       | boolean，默认为true | 请输入大于0的整数                    |
| max_length   | int                 | 最多可以输入 {0} 个字符              |
| min_length   | int                 | 最少要输入 {0} 个字符                |
| range_length | int,int             | 请输入长度在 {0} 到 {1} 之间的字符串 |
| range        | int,int             | 请输入范围在 {0} 到 {1} 之间的数值   |
| max          | int                 | 请输入不大于 {0} 的数值              |
| min          | int                 | 请输入不小于 {0} 的数值              |

