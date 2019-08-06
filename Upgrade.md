1. (升级至composer版本)修改原来buider类的命名空间, 修改Common/Conf/config.php里的相关配置 
     检查根目录与www目录下的文件差异，升级至最新文件
     从最新代码移植lara文件夹到根目录
     升级Common/Common/function.php
2. (升级至v2.0.0版本)修改AppInitBehavior 队列相关类的命名空间，修改Common/Conf/config.php 跟队列有关的设置，从env读取
    修改Common/Model/QueueModel类的队列命名空间，升级后注意旧版队列的前缀会和新版队列的前缀会不一样，新版队列前缀会根据设置的值而定，旧版则不受设定前缀影响
    修改app/resque文件
3. (升级至v2.0.0版本)修改Home/Controller/ElasticsearchController的Elasticsearch类的命名空间，修改Model里跟Elasticsearch相关的命名空间
    修改app/makeIndex.php文件 