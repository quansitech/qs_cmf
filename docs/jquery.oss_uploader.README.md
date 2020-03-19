## oss_uploader oss上传图片

### 介绍

图片裁剪上传功能,内置cropper.js,可配置是否裁剪,可配置oss。

### 功能：

* 可配置使用oss
* 可配置是否裁剪
* 可以定义裁剪尺寸,裁剪比例
* 限制上传张数
* 可自定义提示函数

### 初始化调用

```console
$(selector).ossuploader(option); //selector 为隐藏域

option: {
    url:                //string require  上传图片的地址
    multi_selection:    //boolean optional 是否多选
    oss:                //boolean optional 是否启用oss
    crop:{              //object optional cropper配置,若存在此项，则裁剪图片,更多配置请参考cropper.js官网
        aspectRatio: 120/120,
        viewMode: 1,
        ready: function () { 
            croppable = true;
        }
























    },
    show_msg:           //function optional 展示提示消息的函数,默认为window.alert
    limit:              //number optional 上传图片张数的限制,默认值32
    beforeUpload:       //function optional 回调 参考回调说明
    uploadCompleted:    //function optional 回调 参考回调说明
}
```
备注:
1. cropper：
    - <a href="https://fengyuanchen.github.io/cropper/">官网demo</a>  
    - <a href="https://github.com/fengyuanchen/cropper/blob/master/README.md">github</a>

2. 回调说明:
    - beforeUpload : 当选中文件时的回调。若返回false,则不添加选中的文件
    - uploadCompleted : 上传成功的回调


## 上传回调扩展
<a href="https://github.com/viki719632/think-core/blob/master/asset/libs/ossuploader/oss_upload_extend.README.md">oss上传扩展介绍</a> 



