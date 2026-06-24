<?php
/**
 * 示例 spec — 演示方式 A / 方式 B：捕获并断言 Eloquent 生成的 SQL。
 *
 * 方式 A：被测代码直接用 Model 静态/实例方法（Model::where(...)、$model->where(...)->first()）。
 * 方式 B：被测代码用查询构造器（Capsule::table('x')、DB::table('x')）。
 *
 * 两者的统一策略：用 captureSql() 捕获 SQL，断言其结构与绑定值；用 createTablesFor()
 * 在内存库建空表，让查询返回空结果（而非「no such table」），使被测逻辑跑完整流程。
 */

use App\Models\User;
use Illuminate\Database\Capsule\Manager as Capsule;

describe('SQL 捕获断言', function () {

    beforeAll(function () {
        // 套件级一次性建表：避免每个用例重复建
        createTablesFor([User::class]);
    });

    beforeEach(function () {
        resetSqlBuffer();
    });

    describe('方式 A：被测代码直接用 Model 方法', function () {

        describe('User::getUserByEmailOrNickName', function () {

            it('传入邮箱时按 email 字段查询', function () {
                $sqls = captureSql(function () {
                    (new User())->getUserByEmailOrNickName('test@example.com');
                });

                expect($sqls)->toHaveLength(1);
                expect($sqls[0]['sql'])->toContain('select * from "user"');
                expect($sqls[0]['sql'])->toContain('"email" = ?');
                expect($sqls[0]['bindings'])->toBe(['test@example.com']);
            });

            it('传入非邮箱字符串时按 nick_name 字段查询', function () {
                $sqls = captureSql(function () {
                    (new User())->getUserByEmailOrNickName('tider');
                });

                expect($sqls[0]['sql'])->toContain('"nick_name" = ?');
                expect($sqls[0]['sql'])->not->toContain('"email"');
                expect($sqls[0]['bindings'])->toBe(['tider']);
            });

            it('表存在时查询返回 null（无记录）而非抛异常', function () {
                $result = null;
                captureSql(function () use (&$result) {
                    $result = (new User())->getUserByEmailOrNickName('nobody@example.com');
                });

                expect($result)->toBeNull();
            });
        });
    });

    describe('方式 B：被测代码用 Capsule::table / DB::table 查询构造器', function () {

        beforeAll(function () {
            // 方式 B 用到的表名不一定对应某个模型，需自行建表
            if (!Capsule::schema()->hasTable('syslogs')) {
                Capsule::schema()->create('syslogs', function ($t) {
                    $t->id();
                    $t->integer('userid');
                    $t->string('message')->nullable();
                    $t->integer('create_time')->nullable();
                });
            }
        });

        it('捕获 Capsule::table 链式查询的 SQL 与绑定值', function () {
            $sqls = captureSql(function () {
                Capsule::table('syslogs')
                    ->where('userid', 5)
                    ->where('message', 'like', '登录%')
                    ->orderBy('create_time', 'desc')
                    ->get();
            });

            expect($sqls)->toHaveLength(1);
            expect($sqls[0]['sql'])->toContain('from "syslogs"');
            expect($sqls[0]['sql'])->toContain('"userid" = ?');
            expect($sqls[0]['sql'])->toContain('"message" like ?');
            expect($sqls[0]['sql'])->toContain('order by');
            expect($sqls[0]['bindings'])->toBe([5, '登录%']);
        });
    });
});
