# 静态资源配置 CDN

```text
当网站访问量提升到一定程度，通常会使用CDN来加速网站的访问速度。
但是全站使用CDN时，会收到CDN的限制，例如上传文件时不能超过一定的大小。
可以使用此功能只配置静态资源、用户上传的文件等使用CDN，其他文件不使用CDN。
```



+ 添加 ENV，配置 CDN 链接
  
  ```env
  INJECT_CDN_URL=YOUR_CDN_URL
  ```
  
  
  
+ 修改 asset 函数路径前缀
  
  + 修改 config.php 配置文件
  
    ```php
    //资源调用设置
    'ASSET' => array(
        'prefix' => injecCdntUrl().__ROOT__ . '/Public/',
    ),
    ```
  
    
  
+ 若使用了 imageproxy 来处理图片，以下方式选其中一种即可
  
  + 修改 env 的 IMAGEPROXY_REMOTE 配置，为 CDN 的链接
    ```env
    IMAGEPROXY_REMOTE=YOUR_CDN_URL
    ```
    
  + 若 imageproxy 配置是本地服务器处理，则修改 nginx 配置，重定向图片处理
    ```env
    // env 配置为
    IMAGEPROXY_URL={schema}://{domain}/ip/{options}/{remote_uri}
    ```
    [参考 nginx 配置，重定向 ip 模块资源](https://github.com/quansitech/coding-exp/blob/main/nginx/rewrite_by_map.md)
    
    
  
+ 当上传资源到本地服务器时，检测业务代码返回文件相对路径的地方，加上全局函数 injecCdntUrl 来注入 CDN 域名
  ```php
  // eg
  injecCdntUrl().UPLOAD_DIR . '/' . $file_pic_ent['file'],$file_pic_ent['title']
  ```

  
  
+ 富文本上传的资源使用CDN
  [传送门](https://github.com/quansitech/qscmf-formitem-ueditor/blob/master/README.md)

  
  
+ 前后端分离项目处理，以下方式选其中一种即可
  
  + 修改环境变量 PUBLIC_URL 为 CDN 路径
  + [参考 nginx 配置，重定向静态资源文件](https://github.com/quansitech/coding-exp/blob/main/nginx/rewrite_by_map.md)