#### v12升级步骤

+ 将qs_cmf中的lara/config/cache.php文件复制到lara/config目录

+ CompareBuilder FormBuilder ListBuilder 废弃display方法，需用build方法替换

#### 使用php8.0的修改

+ 检测非常量值是否缺少引号

    + 模型层 $_validate 自动验证 in 缺少引号，搜索 *, in,*

        + AddonsModel

        + AccessModel

        + ConfigModel

        + HooksModel

        + MenuModel

        + NodeModel

        + UserModel

+ 检查函数传参的类型个数与函数要求是否一致

    + 参数类型为数组，建议先判断，再传参；或者强制转换成数组

        + count

        + array_column

        + implode

        + array_merge

        + array_combine

        + array_slice

        + array_map

        + array_diff

        + array_filter

        + array_keys

        + array_intersect

    + 检查ListBuilder addTableColumn type为fun时，回调对应参数是否符合要求

        + 时间类展示建议使用type为date、time替换，如

          ```php
          // 当application_date不存在会报错，若默认为0则会显示 1970年的日期
          ->addTableColumn('application_date', '借阅申请时间', 'fun','date("Y-m-d H:i:s", __data_id__)')
          // 替换
          ->addTableColumn('application_date', '借阅申请时间', 'time')
          ```

    + 检查模型层 $_validate中的 callback

    + 检查模型层 $_auto中的 callback

+ *each* 内置函数移除，需使用 *foreach* 代替

    + ```php
        // 修改C123.class.php文件
    
        // 约23行 
        while (list($k,$v) = each($data))  
        // 改为 
        foreach ($data as $k=>$v)
      ```

    + ```php
        // 修改PHPMailer.class.php文件
        // 检查继承父类是否存在，否则改成 extends \Common\Util\Mail\Driver
        
        // 约1775行
         while( list(, $line) = each($lines) ) { 
        // 改为 
        foreach($lines as $line) {
      ```

    + ```php
        // 修改SMTP.class.php文件
        
        // 约393行 
        while(list(,$line) = @each($lines)) { 
        // 改为 
        foreach($lines as $line) {
        
        // 约422行 
        while(list(,$line_out) = @each($lines_out)) { 
        // 改为 
        foreach($lines_out as $line_out) {
      ```

    + 其它程序需自行检查

+ get_magic_quotes_gpc()内置函数移除，使用 false 替换

**移除函数其它解决方案：已理解原函数的输入输出值后创建一个同名全局函数。**

#### 需要注意使用php8.0不兼容的变更

+ pdo 事务中[隐形提交的sql](https://dev.mysql.com/doc/refman/8.0/en/implicit-commit.html)将会使事务失效，因为它无论如何都不能回滚。如创建、修改、删除数据表的sql

    - 检查数据迁移 DB::beginTransaction() 是否有符合条件的sql

+ 字符串与数字的比较：数字与非数字形式的字符串之间的非严格比较**现在将首先将数字转为字符串，然后比较这两个字符串；之前是将字符串转为数字，然后比较这两个数字**

+ 被除数不能为0

+ 可选参数之后不能使用使用必需的参数，**默认值为null除外，因为这会被认为是null类型**
