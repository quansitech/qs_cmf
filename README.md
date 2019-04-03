# qscmf

![lincense](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)
[![LICENSE](https://img.shields.io/badge/license-Anti%20996-blue.svg)](https://github.com/996icu/996.ICU/blob/master/LICENSE)
![Pull request welcome](https://img.shields.io/badge/pr-welcome-green.svg?style=flat-square)

## 介绍
快速搭建信息管理类系统的框架。基于tp3.2开发，但修改了部分tp源码 ，如该框架支持嵌套事务（原tp3.2不支持嵌套事务）。除此外还集成了众多信息管理系统用到的功能，如队列、插件系统、后台表单生成器、列表生成器等。

## 截图
<img src="https://user-images.githubusercontent.com/1665649/55472251-36458e80-563e-11e9-87c0-10c386d5bd78.png" />

## 安装
```
git clone https://github.com/tiderjian/qs_cmf.git
```

composer安装
```
composer require tiderjian/lara-for-tp
```

安装完成后将tiderjian/lara-for-tp下的stub所有文件复制到qs_cmf项目的根目录，并将qs_cmf\migrations复制到lara\database\migrations，配置lara\.env的数据库设置，运行
```
php artisan migrate
```

## 文档
文档是不存在的，该项目是佛性开源，或许某一天会有吧。。

## lincense
[MIT License](https://github.com/tiderjian/lara-for-tp/blob/master/LICENSE.MIT) AND [996ICU License](https://github.com/tiderjian/lara-for-tp/blob/master/LICENSE.996ICU)
