<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InitDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access', function (Blueprint $table) {
            $table->smallInteger('role_id', false, true);
            $table->smallInteger('node_id', false, true);
            $table->tinyInteger('level');
            $table->string('module', 50)->nullable();
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('area', function(Blueprint $table){
            $table->integer('id')->primary();
            $table->string('cname', 100);
            $table->string('cname1', 50);
            $table->integer('upid');
            $table->string('ename', 100);
            $table->string('pinyin', 100);
            $table->tinyInteger('level');
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        $areas = require database_path('migrations/data/area_data.php');

        DB::table('area')->insert($areas);

        Schema::create('config', function(Blueprint $table){
            $table->unsignedInteger('id', true)->comment('配置ID');
            $table->string('name', 30)->default('')->comment('配置名称');
            $table->string('type', 20)->default(0)->comment('配置类型');
            $table->string('title', 50)->default('')->comment('配置说明');
            $table->unsignedTinyInteger('group')->default(0)->comment('配置分组');
            $table->string('extra', 255)->default('')->comment('配置值');
            $table->string('remark', 100)->comment('配置说明');
            $table->unsignedInteger('create_time')->default(0)->comment('创建时间');
            $table->unsignedInteger('update_time')->default(0)->comment('更新时间');
            $table->tinyInteger('status')->default(0)->comment('状态');
            $table->text('value')->comment('配置值');
            $table->unsignedSmallInteger('sort')->default(0)->comment('排序');
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        $configs = require database_path('migrations/data/config_data.php');
        $configs = array_map(function($c){ unset($c['id']); return $c; }, $configs);
        DB::table('config')->insert($configs);

        Schema::create('file_pic', function(Blueprint $table){
            $table->bigIncrementsForQscmf('id');
            $table->string('title', 200)->default('');
            $table->string('file', 100)->default('');
            $table->string('url', 500)->default('');
            $table->string('ref_id', 200)->default('');
            $table->boolean('ref_status')->default(0)->comment('关联处理标记 如七牛的媒体转码，0 未完成 1已完成');
            $table->string('ref_info', 2000)->default('')->comment('关联处理额外信息，如七牛转码处理失败信息');
            $table->integer('size');
            $table->float('duration', 20, 6)->default(0)->comment('音视频时长');
            $table->string('cate', 50);
            $table->tinyInteger('security')->default(0);
            $table->integer('owner')->default(0);
            $table->integer('upload_date')->default(0);
            $table->tinyInteger('seed')->default(0);
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });


        Schema::create('menu', function(Blueprint $table){
            $table->integerIncrements('id');
            $table->string('title', 50);
            $table->tinyInteger('status');
            $table->smallInteger('sort');
            $table->string('type', 25);
            $table->string('icon', 50);
            $table->string('url', 2000)->default('');
            $table->integer('pid')->default(0);
            $table->integer('level');
            $table->string('module', 100)->default('');
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        $menus = require database_path('migrations/data/menu_data.php');
        $menuIdMap = $this->insertWithAutoIncrement('menu', $menus, ['pid']);

        Schema::create('node', function(Blueprint $table){
            $table->unsignedSmallInteger('id', true);
            $table->string('name', 50);
            $table->string('title', 50);
            $table->tinyInteger('status')->default(0);
            $table->string('remark', 255)->default('');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->unsignedSmallInteger('pid')->default(0);
            $table->unsignedTinyInteger('level')->default(0);
            $table->integer('menu_id')->default(0);
            $table->string('icon', 50)->default('');
            $table->string('url', 500)->default('');
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        $nodes = require database_path('migrations/data/node_data.php');
        $this->insertWithAutoIncrement('node', $nodes, ['pid'], ['menu_id' => $menuIdMap]);

        Schema::create('post', function(Blueprint $table){
            $table->integerIncrements('id');
            $table->string('title', 50)->comment('标题');
            $table->integer('cate_id')->comment('所属分类');
            $table->string('summary', 200)->comment('摘要');
            $table->integer('cover_id')->comment('封面');
            $table->smallInteger('sort')->comment('排序');
            $table->integer('publish_date')->comment('发布时间');
            $table->string('author', 50)->comment('作者');
            $table->string('url', 500)->comment('url');
            $table->string('video', 2000);
            $table->text('content')->comment('正文内容');
            $table->string('images', 1000)->comment('图片');
            $table->string('attach', 1000)->comment('附件');
            $table->tinyInteger('status')->comment('状态');
            $table->string('english_name', 50);
            $table->tinyInteger('up');
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('post_cate', function(Blueprint $table){
            $table->integerIncrements('id');
            $table->string('name', 50)->comment('分类');
            $table->integer('pid')->comment('上级分类');
            $table->string('summary', 200)->comment('摘要');
            $table->integer('cover_id')->comment('分类封面');
            $table->smallInteger('sort')->comment('排序');
            $table->string('url', 500)->comment('url');
            $table->text('content')->comment('分类详情');
            $table->tinyInteger('status')->comment('状态');
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('queue', function(Blueprint $table){
            $table->string('id', 100)->primary();
            $table->string('job', 100);
            $table->string('args', 2000);
            $table->string('description', 200);
            $table->tinyInteger('status');
            $table->integer('create_date');
            $table->string('schedule', 50)->default('');
            $table->string('queue', 50);
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('role', function(Blueprint $table){
            $table->unsignedSmallInteger('id', true);
            $table->string('name', 20);
            $table->smallInteger('pid')->default(0);
            $table->unsignedTinyInteger('status')->nullable()->default(null);
            $table->string('remark', 255)->nullable()->default(null);
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('role_user', function(Blueprint $table){
            $table->unsignedMediumInteger('role_id')->nullable()->default(null);
            $table->char('user_id', 32)->nullable()->default(null);
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('schedule', function(Blueprint $table){
            $table->string('id', 50)->primary();
            $table->integer('run_time')->default(0);
            $table->string('desc', 200)->default('');
            $table->string('preload', 2000)->default('');
            $table->tinyInteger('delete_status')->default(0);
            $table->integer('create_date')->default(0);
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('syslogs', function(Blueprint $table){
            $table->integerIncrements('id');
            $table->string('modulename', 30)->default('');
            $table->string('actionname', 30)->default('');
            $table->string('opname', 30)->default('');
            $table->text('message');
            $table->string('userid', 64)->default('');
            $table->string('userip', 40);
            $table->integer('create_time');
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('user', function(Blueprint $table){
            $table->bigIncrementsForQscmf('id');
            $table->string('nick_name', 30);
            $table->string('pwd', 255)->comment('密码hash（password_hash）');
            $table->string('email', 100)->comment('E-mail');
            $table->string('telephone', 50)->comment('手机号码');
            $table->integer('register_date');
            $table->tinyInteger('status');
            $table->integer('last_login_time')->default(0);
            $table->string('last_login_ip', 20)->default('');
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        $user = [
            'nick_name' => 'admin',
            'pwd' => '$2y$10$uzQAmdyLqKe.XKjg74ibvufh5uF2ERnazogAfE9K3rOw2UDCWqWqK',
            'email' => 'admin@admin.com',
            'telephone' => '15300000000',
            'register_date' => 1464594432,
            'status' => 1,
            'last_login_time' => 1552356067,
            'last_login_ip' => '10.0.1.1',
        ];
        DB::table('user')->insert($user);

        $tablePrefix = DB::getTablePrefix();
        $node_v = <<<SQL
create view {$tablePrefix}node_v as
select n3.id, n1.name "module",n2.name "controller",n3.name "action", CONCAT(n1.name, '.', n2.name, '.', n3.name) node, CONCAT(n1.title, '.', n2.title, '.', n3.title) title
from {$tablePrefix}node n3
inner join {$tablePrefix}node n2 on n2.id=n3.pid and n2.status=1 and n2.level=2
inner join {$tablePrefix}node n1 on n1.id=n2.pid and n1.status=1 and n1.level=1
where n3.level=3 and n3.status=1;
SQL;
        DB::unprepared($node_v);
    }

    /**
     * 插入数据并自动建立ID映射关系
     * 按 level 排序确保父记录先插入，将旧ID映射到自增ID
     *
     * @param string $table 表名
     * @param array $records 包含 'id' 和 'level' 字段的记录数组
     * @param array $fkFields 需要映射的自身外键字段（如 ['pid']）
     * @param array $externalIdMap 外部表ID映射 [字段名 => [旧id => 新id]]
     * @return array 旧ID到新ID的映射
     */
    protected function insertWithAutoIncrement(string $table, array $records, array $fkFields = ['pid'], array $externalIdMap = []): array
    {
        usort($records, function($a, $b) {
            $levelA = intval($a['level'] ?? 0);
            $levelB = intval($b['level'] ?? 0);
            if ($levelA !== $levelB) {
                return $levelA - $levelB;
            }
            return intval($a['id']) - intval($b['id']);
        });

        $idMap = [];
        foreach ($records as $record) {
            $oldId = $record['id'];
            unset($record['id']);

            foreach ($fkFields as $fk) {
                if (!empty($record[$fk]) && isset($idMap[$record[$fk]])) {
                    $record[$fk] = $idMap[$record[$fk]];
                }
            }

            foreach ($externalIdMap as $field => $mapping) {
                if (!empty($record[$field]) && isset($mapping[$record[$field]])) {
                    $record[$field] = $mapping[$record[$field]];
                }
            }

            $newId = DB::table($table)->insertGetId($record);
            $idMap[$oldId] = $newId;
        }

        return $idMap;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tablePrefix = DB::getTablePrefix();
        DB::unprepared('drop view if exists ' . $tablePrefix . 'node_v');

        Schema::dropIfExists('access');
        Schema::dropIfExists('area');
        Schema::dropIfExists('config');
        Schema::dropIfExists('file_pic');
        Schema::dropIfExists('menu');
        Schema::dropIfExists('node');
        Schema::dropIfExists('post');
        Schema::dropIfExists('post_cate');
        Schema::dropIfExists('queue');
        Schema::dropIfExists('role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('schedule');
        Schema::dropIfExists('syslogs');
        Schema::dropIfExists('user');
    }
}
