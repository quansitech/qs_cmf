#### v12升级步骤

+ 将qs_cmf中的lara/config/cache.php文件复制到lara/config目录

+ CompareBuilder FormBuilder ListBuilder 废弃display提示，可用build方法替换
  
+ 修改composer.json文件，替换*require-dev*中的以下包的引入
  ```php
    "phpunit/phpunit": "^8.0",
    "laravel/dusk": "^5.0",
    "mockery/mockery": "^1.2",
    "fzaninotto/faker": "^1.4"
  ```

+ Larafortp/MenuGenerate已经删除，检查项目有无使用，使用 https://github.com/quansitech/qscmf-utils 的 MenuGenerate 代替

+ Larafortp/ConfigGeneratory已经删除，请使用 https://github.com/quansitech/qscmf-utils 的 ConfigGenerator 代替

+ Qscmf/Lib/RedisLock已经删除，请使用 https://github.com/quansitech/qscmf-utils 的 RedisLock代替

+ Larafortp/CmmMigrate/CmmProcess已经删除，请使用 https://github.com/quansitech/qscmf-utils 的 CmmProcess 代替

+ 全局方法 imageproxy已经删除，请使用 https://github.com/quansitech/qscmf-utils 的 Common::imageproxy 代替

+ QsController 取消引入trait类 \Qscmf\Builder\TSubBuilder，如果项目有使用该trait提供的方法，请自行在对应的controller引入

+ QsPage 原来的全局静态方法setPullStyle更名为setDefaultPullStyle，并且新增setPullStyle的对象方法。
  
  升级建议：检查有无使用setPullStyle静态方法，改名为setDefaultPullStyle。有些项目的导出excel功能采用了QsPage对象来获取当前页码，如果后台全局关闭了下拉风格，必须在导出前通过setPullStyle方法重置成下拉风格，否则会无法结束导出程序。

+ 拆分config.php文件
  + 同目录下新增文件夹*Config*，将新增配置文件放在此目录
  + 新增*upload_config.php*文件，并移动与上传相关的配置
    ```php
    //阿里云oss
    'ALIOSS_ACCESS_KEY_ID' => env('ALIOSS_ACCESS_KEY_ID'),
    'ALIOSS_ACCESS_KEY_SECRET' => env('ALIOSS_ACCESS_KEY_SECRET'),

    'UPLOAD_FILE_SIZE' => 50,
    
    'UPLOAD_TYPE_EDITOR' => [...],
    'UPLOAD_TYPE_AUDIO' => [...],
    'UPLOAD_TYPE_IMAGE' => [...],
    'UPLOAD_TYPE_VIDEO' => [...],
    'UPLOAD_TYPE_FILE' => [...],
    'UPLOAD_TYPE_SIMAGE' => [...],
    'UPLOAD_TYPE_JOB_IMAGE' => [...],
    ```
  + 新增*http_config.php*文件，并移动http协议相关配置
    ```php
    //通过$_SERVER数组获取当前访问的http协议的关键值
    'HTTP_PROTOCOL_KEY' => 'REQUEST_SCHEME',
    ```
  + 移除*config.php*以上配置项，并将以上新增配置文件合并至同一数组
    ```php
    // 第二行    
    return array(
    // 改为
    $common_config = array(
    
    // 在尾部追加文件
    return array_merge($common_config, loadAllCommonConfig());
    ```
  

#### 使用php8.0的修改

+ 检测非常量值是否缺少引号
  
  + 模型层 $_validate 自动验证 in 缺少引号，搜索 *, in,*
    
    + AddonsModel
    
    + AccessModel
    
    + ConfigModel
    
    + HooksModel
    
    + MenuModel
    
    + NodeModel
      
    + RoleModel
    
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

+ get_magic_quotes_gpc()内置函数移除，全局搜索此函数并使用 false 替换

+ 检查复杂正则表达式是否能执行
  
  + ```php
    // 修改Common\Util\AliOss.class.php文件
    
    // 约36行，正则表达式改为
    // 中划线是特殊字符，需要使用反斜杠
    '/https*:\/\/([\w\-_]+?)\.[\w\-_.]+/' 
    ```

**移除函数其它解决方案：已理解原函数的输入输出值后创建一个同名全局函数。**

#### 需要注意使用php8.0不兼容的变更

+ 字符串与数字的比较：数字与非数字形式的字符串之间的非严格比较**现在将首先将数字转为字符串，然后比较这两个字符串；之前是将字符串转为数字，然后比较这两个数字**

+ implode()函数，在 PHP 8.0 之前可以接收两种参数顺序，之后必须保证 separator 参数在 array 参数之前。

+ 内置函数参数个数与数据类型需要一致
  + ```php
    // time() 不接收参数
    // microtime([]) 参数类型必须为float或者string
    ```
