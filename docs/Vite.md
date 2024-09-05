# qscmf Vite使用说明

1. 集成vite构建前端资源，默认使用react 及 typescript。
2. 集成inertia。

## 目录

```text
resources
 |___ js
 |     |___ frontend  // 前台资源
 |     |     |___ Pages  // 页面组件目录
 |     |     |___ app.jsx  // 入口文件
 |     |___ backend  // 后台资源
 |     |     |___ Pages  // 页面组件目录
 |     |     |___ app.jsx  // 入口文件
vite.config.js // 前台vite配置文件
vite.backend.config.js // 后台vite配置文件
```

## 使用

1. 安装依赖包

```shell
npm install
```

2. 新增页面组件

```tsx
// resources/js/frontend/Pages/Index.tsx
import {usePage, Head} from "@inertiajs/react";

export default function (){
    const props = usePage<{
        fooUrl: string,
        barUrl: string
    }>().props

    return <>
        <Head title="Index"></Head>
        <h1>TP inertia</h1>
    </>
}
```

3. 编译静态资源

```shell
# 前台编译
npm run build
# 后台编译
npm run build:backend
```

4. controller中返回inertia响应

```php
$this->inertia('Index', [
    // 页面的props
    'foo' => 'bar'
])
```

### 开发

可跳过上述 ```3.编译静态资源``` 步骤，直接查看效果

```php
# 前台开发
npm run dev
# 后台开发
npm run dev:backend
```

增加环境变量到.env

```dotenv
APP_URL=http://[开发主机地址]/
```

## SSR 服务端渲染

1. 修改resources/js/frontend/app.jsx

```tsx 
import {createRoot, hydrateRoot} from 'react-dom/client'

//...省略代码

//将
createRoot(el).render(<App {...props} />)
//替换为
hydrateRoot(el, <App {...props} />)

//...省略代码
```

2. 编译ssr资源

```shell
npm run build:ssr
```

3. 运行ssr服务

```shell
npm run ssr:serve
```

4. 增加配置到.env

```dotenv
# SSR 服务端地址
INERTIA_SSR_URL=http://[ssr主机]:13714
```