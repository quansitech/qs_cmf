### 使用react构建后台页面
```text
当后台页面排版比较特别或者是交互逻辑比较复杂时，可以使用 react 自定义一个页面。
```


#### 用法
```text
自定义 admin/ReactBuildTest/index.html 页面为例
```

+ 创建 ReactBuildTestController 控制器及其对应接口
  ```php
  // 界面渲染
  public function index(){

        $status_list = collect(DBCont::getApplicatStatusList())->map(Fn($name,$key)=>['value'=>$key,'label'=>$name])->values()->all();
        $this->assign("status_list", $status_list);
        $this->display();
  }
  
  // 实现接口
  public function getTest($stage_id){
        if ($stage_id%2!==0){
            $this->ajaxReturn(['status' => 1, 'data' => ['stage_id'=> $stage_id]]);
        }else{
            $this->ajaxReturn(['status' => 0, 'info' => "失败用例"]);
        }
  }
  ```

+ 在 src/api-url.js 中追加已新增的接口
  ```php
  reactBuildTestGet: '/admin/ReactBuildTest/getTest',
  ```

+ 使用函数访问接口
  ```text
  可以按照功能模块或者控制器定义不同的请求类对象
  ```
  + 创建 src/react-build-test-request.js 文件
  + 在 src/react-build-test-request.js 文件，关联已新增的接口
  ```php
  const http = new Http();
  
  const ReactBuildTestRequest = {
    getTest: stageId => http.get(ApiUrl.reactBuildTestGet+'?stage_id='+stageId),
  }
  
  export default ReactBuildTestRequest;
  ```
 
+ 在 src/template/ 创建 react-build-test.ejs 模板文件
  ```php
  // 定义 css 或者 js 文件占位符；定义直接渲染的变量等
    <extend name="Admin@default/common/dashboard_layout" />
    <block name="content">
        <include file="Public/vendor"/>
        <!-- Content Header (Page header) -->
        <section class="content-header clearfix" >
            <h1>
                ReactBuildTest
            </h1>
        </section>
        <!-- Main content -->
        <section class="content" n-id="{$nid}">
            <div id="app" style="overflow-x: hidden;"></div>
        </section>
        <script>
            var statusList = {:json_encode($status_list, JSON_PRETTY_PRINT)};
        </script>
        <%= htmlWebpackPlugin.tags.headTags %>
    
    
        <!-- /.content -->
    </block>
  ```
  
+ 在 src/vendor.js 中加入antd或者自定义的基础组件
  ```text
  如antd的Table、Select、Button等基础组件
  如基于Button组件结合项目定义样式且解决了节流等问题而封装的基础组件
  ```

+ 在 src/page/ 创建 react-build-test.js 文件
  ```javascript
  // 引入接口文件
  import reactBuildTestReq from "../common/react-build-test-request";
  
  const useState = React.useState;
  const useEffect = React.useEffect;
  
  const ReactBuildTest = function(props){
  const [stageId, setStageId] = useState(1);

  useEffect(() => {
        const init = async () => {
            // 调用接口
            const res = await reactBuildTestReq.getTest(1);
            if (parseInt(res.status) === 1){
                setStageId(res.data.stageId);
            }
        }
        init();
  }, [])
  
  return <>
       <div>ReactBuildTest</div>
       <div>{stageId}</div>
  </>
  }
    
  ReactDOM.render(<ReactBuildTest
    statusList={ statusList }
  />, document.getElementById('app'));
  ```

+ 修改 src/webpack_config/webpack.common.js ，添加构建 react-build-test 文件
  ```javascript
    entry: {
        // 添加
        reactBuildTest: './src/page/react-build-test.js'
    },
    plugins: [
        // ......
        // 添加
        new HtmlWebpackPlugin({
            filename: path.join(
                __dirname,
                '../../app/Admin/View/default/ReactBuildTest',
                'index.html'
            ),
            template: path.join(__dirname, '../src/template', 'react-build-test.ejs'),
            inject: false,
            chunks: ['reactBuildTest'],
        }),
    ]
  ```
  
+ 修改.gitignore，将 app/Admin/View/default/ReactBuildTest 移除版本控制
  ```php
  // 追加内容
  /app/Admin/View/default/ReactBuildTest/index.html
  ```


#### 命令介绍
```text
静态资源存放路径为 www/Public/static/dist/admin
```
+ build-page 仅重新构建page
+ build-vendor 仅重新构建vendor
+ build 重新构建page和vendor
+ dev 刷新既可同步修改内容