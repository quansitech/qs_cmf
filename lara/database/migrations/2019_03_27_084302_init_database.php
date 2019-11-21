<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InitDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qs_access', function (Blueprint $table) {
            $table->smallInteger('role_id', false, true);
            $table->smallInteger('node_id', false, true);
            $table->tinyInteger('level');
            $table->string('module', 50)->nullable();
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('qs_addons', function (Blueprint $table) {
            $table->unsignedInteger('id', true)->comment('主键');
            $table->string('name', 40)->comment('插件名或标识');
            $table->string('title', 20)->default('')->comment('中文名');
            $table->text('description')->comment('插件描述');
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->text('config')->default(null)->nullable()->comment('配置');
            $table->string('author', 40)->default('')->comment('作者');
            $table->string('version', 20)->default('')->comment('版本号');
            $table->unsignedInteger('create_time')->default(0)->comment('安装时间');
            $table->unsignedTinyInteger('has_adminlist')->default(0)->comment('是否有后台列表');
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('qs_area', function(Blueprint $table){
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

        DB::table('qs_area')->insert($areas);

        Schema::create('qs_coder_log', function(Blueprint $table){
            $table->integer('id', true);
            $table->string('coder_name', 30);
            $table->text('content');
            $table->integer('create_date');
            $table->string('name', 50);
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        $coderLogs = require database_path('migrations/data/coder_log_data.php');;

        DB::table('qs_coder_log')->insert($coderLogs);

        Schema::create('qs_config', function(Blueprint $table){
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


        DB::table('qs_config')->insert($configs);

        Schema::create('qs_file_pic', function(Blueprint $table){
            $table->bigIncrements('id');
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


        Schema::create('qs_hooks', function(Blueprint $table){
            $table->integerIncrements('id');
            $table->string('name', 100);
            $table->string('desc', 500);
            $table->integer('update_date');
            $table->tinyInteger('status');
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        $hooks = require database_path('migrations/data/hooks_data.php');


        DB::table('qs_hooks')->insert($hooks);

        Schema::create('qs_js_errlog', function(Blueprint $table){
            $table->integerIncrements('id');
            $table->string('browser', 200)->default('');
            $table->string('msg', 500)->default('');
            $table->string('file', 500)->default('');
            $table->unsignedInteger('line_no')->default(0);
            $table->unsignedInteger('col_no')->default(0);
            $table->string('stack', 2000)->default('');
            $table->string('user_agent', 1000)->default('');
            $table->string('url', 500)->default('');
            $table->integer('create_date')->default(0);
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });


        Schema::create('qs_menu', function(Blueprint $table){
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

        DB::table('qs_menu')->insert($menus);

        Schema::create('qs_node', function(Blueprint $table){
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

        DB::table('qs_node')->insert($nodes);

        Schema::create('qs_post', function(Blueprint $table){
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

        Schema::create('qs_post_cate', function(Blueprint $table){
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

        Schema::create('qs_queue', function(Blueprint $table){
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

        Schema::create('qs_role', function(Blueprint $table){
            $table->unsignedSmallInteger('id', true);
            $table->string('name', 20);
            $table->smallInteger('pid')->default(0);
            $table->unsignedTinyInteger('status')->nullable()->default(null);
            $table->string('remark', 255)->nullable()->default(null);
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('qs_role_user', function(Blueprint $table){
            $table->unsignedMediumInteger('role_id')->nullable()->default(null);
            $table->char('user_id', 32)->nullable()->default(null);
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('qs_schedule', function(Blueprint $table){
            $table->string('id', 50)->primary();
            $table->integer('run_time')->default(0);
            $table->string('desc', 200)->default('');
            $table->string('preload', 2000)->default('');
            $table->tinyInteger('delete_status')->default(0);
            $table->integer('create_date')->default(0);
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });

        Schema::create('qs_syslogs', function(Blueprint $table){
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

        Schema::create('qs_user', function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('nick_name', 30);
            $table->integer('salt');
            $table->string('pwd', 50);
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
            'id' => 1,
            'nick_name' => 'admin',
            'salt' => 487371,
            'pwd' => '9b5240844dedd8003660e6ee0433d6f3',
            'email' => 'admin@admin.com',
            'telephone' => '15300000000',
            'register_date' => 1464594432,
            'status' => 1,
            'last_login_time' => 1552356067,
            'last_login_ip' => '10.0.1.1',
        ];
        DB::table('qs_user')->insert($user);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qs_access');
        Schema::dropIfExists('qs_addons');
        Schema::dropIfExists('qs_area');
        Schema::dropIfExists('qs_coder_log');
        Schema::dropIfExists('qs_config');
        Schema::dropIfExists('qs_file_pic');
        Schema::dropIfExists('qs_hooks');
        Schema::dropIfExists('qs_js_errlog');
        Schema::dropIfExists('qs_menu');
        Schema::dropIfExists('qs_node');
        Schema::dropIfExists('qs_post');
        Schema::dropIfExists('qs_post_cate');
        Schema::dropIfExists('qs_queue');
        Schema::dropIfExists('qs_role');
        Schema::dropIfExists('qs_role_user');
        Schema::dropIfExists('qs_schedule');
        Schema::dropIfExists('qs_syslogs');
        Schema::dropIfExists('qs_user');
    }
}
