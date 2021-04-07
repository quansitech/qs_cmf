### 消息队列

#### 用法

##### 添加队列

##### 添加计划任务

##### 执行队列
```blade
环境变量参数值：
--queue|QUEUE: 需要执行的队列的名字
--interval|INTERVAL：在队列中循环的间隔时间，即完成一个任务后的等待时间，默认是5秒
--app|APP_INCLUDE：需要自动载入PHP文件路径，Worker需要知道你的Job的位置并载入Job
--count|COUNT：需要创建的Worker的数量。所有的Worker都具有相同的属性。默认是创建1个Worker
--debug|VVERBOSE：设置“1”启用更啰嗦模式，会输出详细的调试信息
--log|LOGGING：设置"1"，输出调试信息
```

```php
php 应用目录/resque start --queue=default
```

##### 安全停止队列
```blade
当前任务执行完后再停止队列
```
```php
php 应用目录/resque stop
```