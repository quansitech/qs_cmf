## 扩展包使用v13修改说明

若符合以下修改要求，需要同步修改 composer.json ，限制 think-core 为 v13 的最新版本

+ **检查实现了 EditableInterface 的 ColumnType，将 builder 传给 getSaveTargetForm 方法。**

+ **检查继承了 ButtonType 的 TopButton，将 build 改成与父类一致。**  
