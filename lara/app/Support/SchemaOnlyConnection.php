<?php
/**
 * SchemaOnlyConnection —— 重放 migration 时屏蔽数据类语句的 SQLite 连接。
 *
 * 继承 SQLiteConnection，重写 SQL 执行入口（statement / affectingStatement /
 * unprepared / insert）。对建表类 DDL（create table/index/unique, alter table,
 * drop）放行给父类正常执行；对数据类语句（insert/update/delete/replace）和视图/
 * 触发器（create view/trigger）直接返回成功值，不触达 PDO。
 *
 * 设计动机：重放 migration 只为抽取表结构（DDL），不应执行 area 3.4 万行 insert 种子
 * （超 SQLite 绑定上限，且对 schema 无价值），也不应执行 node_v/area_v 的 CONCAT
 * 视图（SQLite 无 CONCAT 函数会报错）。在 Connection 层拦截（而非 PDO 层）的优势：
 * - 能拿到编译后的 SQL 和 bindings，精确判断；
 * - 避免在 PDO 层构造占位 SELECT 吸收绑定时的「列数不匹配」问题；
 * - 屏蔽数据语句时直接 return，不创建 PDOStatement。
 *
 * 仅在 App\Support\SchemaCacheBuilder 的临时重放上下文使用，通过
 * Connection::resolverFor('sqlite', ...) 注册为 sqlite driver 的连接解析器。
 */

namespace App\Support;

use Illuminate\Database\SQLiteConnection;

class SchemaOnlyConnection extends SQLiteConnection
{
    /**
     * 执行 prepared statement（建表 DDL、查询走这里）。
     *
     * Schema::create/table 编译出的 create/alter/drop 放行；insert/视图/触发器屏蔽。
     */
    public function statement($query, $bindings = [])
    {
        if ($this->shouldExecute($query)) {
            return parent::statement($query, $bindings);
        }

        return true; // 屏蔽数据类语句，返回「执行成功」
    }

    /**
     * 执行影响行数的语句（update/delete 走这里）。
     */
    public function affectingStatement($query, $bindings = [])
    {
        if ($this->shouldExecute($query)) {
            return parent::affectingStatement($query, $bindings);
        }

        return 0; // 屏蔽，返回「影响 0 行」
    }

    /**
     * 执行未预处理语句（DB::unprepared、create view 走这里）。
     */
    public function unprepared($query)
    {
        if ($this->shouldExecute($query)) {
            return parent::unprepared($query);
        }

        return true; // 屏蔽
    }

    /**
     * 判断语句是否应真正执行（放行），否则屏蔽。
     *
     * 采用「屏蔽列表」正向判断需拦截的语句前缀，其余一律放行：
     * - 屏蔽：insert / update / delete / replace（数据类 DML，含 area 3.4万行种子）
     *         create view / create trigger / create virtual（视图等，含 CONCAT）
     * - 放行：create table / create index / alter table / drop（建表 DDL）
     *         select / pragma（查询，含抽 sqlite_master）
     */
    private function shouldExecute(string $sql): bool
    {
        $lower = ltrim(strtolower($sql));

        // 屏蔽：数据写入类 DML
        if (str_starts_with($lower, 'insert')) {
            return false;
        }
        if (str_starts_with($lower, 'update')) {
            return false;
        }
        if (str_starts_with($lower, 'delete')) {
            return false;
        }
        if (str_starts_with($lower, 'replace')) {
            return false;
        }

        // 屏蔽：视图 / 触发器 / 虚拟表（SQLite 无 CONCAT，node_v/area_v 视图会失败）
        if (str_starts_with($lower, 'create view')) {
            return false;
        }
        if (str_starts_with($lower, 'create trigger')) {
            return false;
        }
        if (str_starts_with($lower, 'create virtual')) {
            return false;
        }

        // 其余（建表 DDL、select、pragma 等）一律放行
        return true;
    }
}
