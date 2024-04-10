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
    

  

+ 子表格为 **array**

  格式为  **['type' => 'sub_table', 'rules' => '同普通字段']**

  | 名称  | 说明           |
  | ----- | -------------- |
  | type  | 恒为 sub_table |
  | rules | 同普通字段     |

