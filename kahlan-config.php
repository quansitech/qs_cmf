<?php
/**
 * Kahlan 配置 — 纯单元测试（捕获/断言 SQL，不连业务库）。
 *
 * 运行：composer test:kahlan
 *
 * 说明：bootstrap 指向 lara/specs/bootstrap.php，它负责加载 autoload、注册
 * think-core 的 .class.php 类加载器、轻量 boot Capsule 连内存 SQLite，
 * 并提供 captureSql() / createTablesFor() / resetSqlBuffer() 等辅助函数。
 */

// 1. 注册 Kahlan DSL 全局函数 shim（解决与 Laravel 11 context() helper 的命名冲突）。
//    必须在 spec 运行前、initKahlanGlobalFunctions() 执行前完成（本文件即 loadConfig 阶段）。
require __DIR__ . '/lara/specs/kahlan-functions.php';

// 2. Kahlan 入口已自行加载 vendor/autoload.php；这里额外 require 我们的环境引导：
//    注册 think-core 的 .class.php 类加载器、轻量 boot Capsule、定义 captureSql() 等辅助函数。
require __DIR__ . '/lara/specs/bootstrap.php';

$commandLine = $this->commandLine();

// spec 目录（与 lara/tests 平级，Kahlan 会递归扫描 *Spec.php）
$commandLine->option('spec', 'default', 'lara/specs');

// 覆盖率统计范围（可选，配合 composer test:kahlan:coverage）
$commandLine->option('src', 'default', ['lara/app']);
