# Upload API 接口文档

## 概述
此文档描述了文件上传相关的API接口，包括前置处理接口和实际上传接口。

## upload 前置接口

### HTTP请求方法
GET

### 路径
`/api/upload`

### 请求参数

| 参数名  | 类型 | 是否必须 | 说明  |
| ------- | ---- | -------- | ----- |
| title   | string | 是      | 文件名 |
| cate    | string | 是      | config上传分类 |
| hash_id | string | 否      | 文件hash_id，可选|

### 返回参数

根据请求成功与否返回不同结构的JSON数据。

成功示例:
```json
{
    "status": 1,
    "file_id": "文件ID",
    "url": "文件访问URL",
    "server_url": "上传文件接口URL"
}
```

文件没有上传过（不传hash_id或者hash_id不符合32位长度都属于文件没有上传过的情景）
```json
{
    "status": 0,
    "server_url": "上传文件接口URL"
}
```

### 错误响应
错误时将抛出异常，格式如下：
```plaintext
缺少参数[参数名]
没有配置UPLOAD_TYPE_[大写的cate]类型的上传配置
```

## uploadFile 上传文件接口

### HTTP请求方法
POST

### 路径
`/api/uploadFile`

### 请求参数

请通过表单形式上传，以下为请求参数说明：

| 参数名 | 类型 | 是否必须 | 说明  |
| ------ | ---- | -------- | ----- |
| cate   | string | 是      | config上传分类 |
| file   | file   | 是      | 要上传的文件 |
| hash_id | string | 否      | 文件hash_id，可选|

### 返回参数

根据请求成功与否返回不同结构的JSON数据。

成功示例:
```json
{
    "status": 1,
    "file_id": "文件ID",
    "url": "文件访问URL"
}
```

失败示例 :
会返回带有错误信息的响应，或抛出异常。

### 错误响应
错误时将抛出异常，格式如下：
```plaintext
没有配置UPLOAD_TYPE_[大写的cate]类型的上传配置
```

上传失败时将调用 `error` 方法返回错误信息。

注意：此文档仅为基于提供的PHP代码生成的接口文档示例，实际API调用请参照服务器的接口部署和配置情况。


## js实现文件上传示例

```javascript
// file是File对象
// hashId是file文件的hashId
let action = '/api/upload/upload?cate=image&title=' + file.name + '&hash_id=' + hashId;

//检验文件是否已经存在
const res = await fetch(action);
const resData = await res.json();
//文件已存在，直接返回文件数据，无需执行文件上传的动作
if(resData.status){
    return resData;
}

//文件不存在，获取server_url，server_url是接收文件上传的接口地址
server_url = resData.server_url;
const formData = new FormData();
formData.append("file", file);

const response = await fetch(server_url, {
  method: "POST",
  body: formData
});

const data = await response.json();

```