## Builder

#### setNID 
```blade
参数 
$nid  需要高亮的左菜单栏的node_id
```

#### setNIDByNode
```blade
该方法是setNID的封装，通过module controller action动态获取nid

参数 
$module 需要高亮左侧菜单的module_nam，默认为MODULE_NAME
$controller 需要高亮左侧菜单的controller_name，默认为CONTROLLER_NAME
$action 需要高亮左侧菜单的action_name，默认为index
```

#### setTopHtml
```blade
该方法用于设置页面顶部自定义html代码

参数
$top_html 顶部自定义html代码
```
截图  
ListBuilder，如图所示

![image](https://user-images.githubusercontent.com/35066497/69775189-fd60b800-11d2-11ea-9438-1a1d3dc9190b.png)

FormBuilder，如图所示

![image](https://user-images.githubusercontent.com/35066497/69775187-fb96f480-11d2-11ea-8447-438dd1585982.png)

CompareBuilder，如图所示

![image](https://user-images.githubusercontent.com/35066497/69775169-ea4de800-11d2-11ea-8a5e-60f6a1f7e792.png)

#### checkAuthNode
```blade
该方法用于检测字段的权限点，无权限则unset该item
```