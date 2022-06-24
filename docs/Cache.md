## Cache

redis 

方法 set

| 参数     | 类型     | 默认值  | 描述                               |
| ------ | ------ | ---- | -------------------------------- |
| name   | string | 无    | 键                                |
| value  | string | 无    | 值                                |
| expire | int    | null | 过期时间，null或者0表示永远不过期              |
| flag   | string | 空    | nx 值不存在则进行设置操作<br/>xx 值存在才进行设置操作 |

```
$redis = Cache::getInstance('redis');
$redis->set('test', 'hello', 3600, 'nx');
```


