<?php
/**
 * SchemaCacheBuilder —— 为 Kahlan 单元测试生成表结构缓存。
 *
 * 设计动机：spec 里 defineTables([...]) 需要程序员手抄一遍表结构，而权威定义在
 * lara/database/migrations/ 下。本类把迁移里的建表逻辑重放到一个临时 SQLite 内存库，
 * 抽出每张表的 CREATE TABLE / CREATE INDEX DDL，dump 成 PHP 数组缓存文件，供
 * lara/specs/bootstrap.php 的 loadCachedSchema() 加载。
 *
 * 技术路线（重放 Blueprint）：
 * - migration 里用的是 Laravel Blueprint 抽象类型（$t->string() / $t->integer() 等），
 *   由 SQLite grammar 自动转成 SQLite 方言——类型兼容性由框架保证，无需手写映射。
 * - 临时 connection 注入自定义 PDO（SchemaOnlyPdo）按 SQL 关键词分流：建表类 DDL
 *   (create table/index/unique, alter table) 放行给真实 PDO，数据类 DML
 *   (insert/update/delete/create view/create trigger/drop) 屏蔽为空操作。
 *   这样 init migration 里 area 3.4 万行 insert 种子、node_v/area_v 的 CONCAT 视图
 *   都被跳过，只留下空表结构。
 * - 按文件名顺序重放所有 migration 的 up()：先 init 建表，后 alter 增列/索引。
 *
 * 触发时机：由 KahlanSchemaCacheServiceProvider 监听 MigrationsEnded('up') 自动调用。
 * production 环境由 provider 守卫跳过，本类不会被调到。
 *
 * 缓存文件：lara/specs/Support/schema_cache.php（已 .gitignore，生成物不进版本管理）。
 */

namespace App\Support;

use Illuminate\Support\Facades\DB;

class SchemaCacheBuilder
{
    /**
     * 临时 SQLite connection 的配置名（避免与生产 connection 冲突）。
     */
    private const TEMP_CONNECTION = '__schema_cache';

    /**
     * 生成并写入 schema 缓存文件。
     *
     * 在调用方（ServiceProvider 的 MigrationsEnded 监听器）里已做 production 守卫，
     * 本方法不做环境判断，假定在 dev/testing 环境、Laravel app 已 boot 完成。
     *
     * @param  string|null $migrationsPath 迁移目录，默认 database/migrations
     * @param  string|null $cacheFile      缓存文件路径，默认 lara/specs/Support/schema_cache.php
     * @return int                         写入的 DDL 条目数
     */
    public static function build(?string $migrationsPath = null, ?string $cacheFile = null): int
    {
        $migrationsPath = $migrationsPath ?? database_path('migrations');
        // base_path() = lara/（Application basePath），specs 在 lara/specs 下
        $cacheFile = $cacheFile ?? base_path('specs/Support/schema_cache.php');

        // 1. 注册临时 SQLite 内存 connection + 切默认连接，让 Schema/DB facade 指向它。
        //    保存原配置以便还原，避免污染 migrate 流程后续逻辑。
        $savedDefault = self::registerTempConnection();

        try {
            // 2. 重放所有 migration 的 up()（DML/视图被 SchemaOnlyPdo 屏蔽，只建空表）。
            self::replayMigrations($migrationsPath);

            // 3. 从 sqlite_master 抽取建表 DDL（排除 migrations 记录表、视图、内部索引）。
            $schema = self::extractDdl();
        } finally {
            // 4. 还原默认连接配置（无论成功失败都还原）。
            self::restoreDefaultConnection($savedDefault);
        }

        // 5. 写缓存文件。
        self::writeCacheFile($cacheFile, $schema);

        return count($schema);
    }

    /**
     * 注册临时 SQLite 内存 connection，并把 config('database.default') 切到它。
     *
     * 通过 Connection::resolverFor('sqlite', ...) 注册 SchemaOnlyConnection 作为
     * sqlite driver 的连接解析器，让临时连接自动屏蔽数据类语句（insert/视图等），
     * 只放行建表 DDL。这样既复用 Laravel 的 connection 组装（grammar/builder/schema），
     * 又在 Connection 层精确拦截，避免 area 3.4 万行种子和 CONCAT 视图。
     *
     * 注意：resolverFor 是静态绑定，重放后会还原（见 restoreDefaultConnection）。
     *
     * @return mixed 原 database.default 配置值（用于还原）
     */
    private static function registerTempConnection()
    {
        // 注册自定义 sqlite 连接解析器：之后创建的 sqlite 连接都用 SchemaOnlyConnection。
        // 保存原 resolver（若有）以便还原，避免影响生产 sqlite 连接。
        $originalResolver = \Illuminate\Database\Connection::getResolver('sqlite');
        \Illuminate\Database\Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
            return new SchemaOnlyConnection($connection, $database, $prefix, $config);
        });

        config([
            'database.connections.' . self::TEMP_CONNECTION => [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ],
        ]);

        $savedDefault = config('database.default');
        config(['database.default' => self::TEMP_CONNECTION]);

        // 把原 resolver 挂到 savedDefault 的附加槽位，restoreDefaultConnection 取回。
        // （用静态变量更清晰，但为保持函数式风格，借用一个内部属性）
        self::$originalSqliteResolver = $originalResolver;

        // macro bigIncrementsForQscmf 内部调 DB::getDriverName()，切换 default 后
        // DB facade 自动指向临时连接（driver=sqlite），macro 的 pgsql 分支被跳过。

        return $savedDefault;
    }

    /**
     * @var \Closure|null sqlite driver 的原始 resolver（还原用）
     */
    private static $originalSqliteResolver = null;

    /**
     * 还原 database.default 配置、sqlite resolver，并清理临时 connection 单例。
     */
    private static function restoreDefaultConnection($savedDefault): void
    {
        config(['database.default' => $savedDefault]);

        // 还原 sqlite driver 的 resolver（避免影响后续创建的 sqlite 连接）。
        if (self::$originalSqliteResolver !== null) {
            \Illuminate\Database\Connection::resolverFor('sqlite', self::$originalSqliteResolver);
        }
        self::$originalSqliteResolver = null;

        // 断开临时 connection 单例，避免下次 migrate 复用被污染的连接。
        $manager = app('db');
        if (method_exists($manager, 'purge')) {
            $manager->purge(self::TEMP_CONNECTION);
        }
    }

    /**
     * 按文件名顺序重放每个 migration 的 up()。
     *
     * require_once 让类定义进入符号表，再 new 实例调 up()。
     * Migration 基类无构造副作用，up() 为 public。
     */
    private static function replayMigrations(string $migrationsPath): void
    {
        $files = glob(rtrim($migrationsPath, '/') . '/*.php');
        sort($files); // 按文件名（时间戳前缀）排序，保证 init 在 alter 之前

        foreach ($files as $file) {
            require_once $file;

            $className = self::resolveMigrationClass(basename($file));
            if (!class_exists($className)) {
                continue;
            }

            $migration = new $className();
            if (method_exists($migration, 'up')) {
                $migration->up();
            }
        }
    }

    /**
     * 从 migration 文件名解析类名。
     *
     * Laravel 迁移命名规范：YYYY_MM_DD_HHMMSS_snake_case_name.php，
     * 类名为时间戳后部分转 StudlyCase（如 init_database → InitDatabase）。
     * 用 Laravel 内置 Str::studly() 保证大小写转换与框架一致。
     */
    private static function resolveMigrationClass(string $basename): string
    {
        $name = preg_replace('/\.php$/', '', $basename);
        $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $name);
        return \Illuminate\Support\Str::studly($name);
    }

    /**
     * 从 sqlite_master 抽取建表与索引 DDL。
     *
     * 过滤规则：
     * - type 仅取 table / index（排除 view，视图含 CONCAT 等不可回放语法）
     * - sql IS NOT NULL（PRIMARY KEY/UNIQUE 自动生成的内部索引 sql 为 NULL）
     * - 排除 migrations 表（迁移记录表，与业务 schema 无关）
     * - 排除 sqlite_ 前缀的系统对象
     *
     * @return array name => ['type' => string, 'sql' => string]
     */
    private static function extractDdl(): array
    {
        $rows = DB::select(
            "SELECT type, name, sql FROM sqlite_master "
            . "WHERE type IN ('table', 'index') "
            . "AND sql IS NOT NULL "
            . "AND name NOT LIKE 'sqlite_%' "
            . "AND name != 'migrations' "
            . "ORDER BY type DESC, name" // table(DESC排前) 先于 index
        );

        $schema = [];
        foreach ($rows as $row) {
            $schema[$row->name] = [
                'type' => $row->type,
                'sql'  => $row->sql,
            ];
        }

        return $schema;
    }

    /**
     * 把 schema 数组 dump 成 PHP 缓存文件。
     */
    private static function writeCacheFile(string $cacheFile, array $schema): void
    {
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $export = var_export($schema, true);

        $content = <<<PHP
<?php
/**
 * Kahlan schema 缓存 —— 由 App\\Support\\SchemaCacheBuilder 自动生成。
 *
 * 生成时间：{$timestamp}
 * 来源：重放 lara/database/migrations/ 的建表逻辑到临时 SQLite 内存库。
 *
 * ⚠️ 这是生成物（derived artifact），请勿手动编辑、请勿提交到版本管理（已 .gitignore）。
 *    修改迁移后跑 php artisan migrate 会自动刷新本文件。
 *
 * 格式：[ 名称 => ['type' => 'table'|'index', 'sql' => 'CREATE ...'] ]
 * 加载：lara/specs/bootstrap.php 的 loadCachedSchema() 读取本文件，在内存库逐条执行 SQL。
 */

return {$export};

PHP;

        file_put_contents($cacheFile, $content);
    }
}
