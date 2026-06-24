<?php
/**
 * spec — 测试 Qscmf\Core\BaseModel 的增删改无侵入规则验证能力。
 *
 * 覆盖五个属性：
 *   - $rules                增改（save）时的验证规则
 *   - $validationMessages   增改验证的自定义错误消息
 *   - $deleteRules          删除（delete）时的验证规则
 *   - $deleteValidationMessages  删除验证的自定义错误消息
 *   - $deleteCascade        删除时联动清理的关联表配置
 *
 * 被测 fixture 模型定义在 Support/FixtureModels/（RuleArticle / RuleNode / RuleAccount）。
 *
 * 注：验证框架（Validator Factory + Event Facade）已由 bootstrap.php 自动初始化，
 * spec 无需关心 watson/validating 的事件机制与全局变量，只需声明表结构 + 写断言。
 */

use Illuminate\Database\Capsule\Manager as Capsule;
use Specs\Support\FixtureModels\RuleAccount;
use Specs\Support\FixtureModels\RuleArticle;
use Specs\Support\FixtureModels\RuleNode;

describe('BaseModel 增删改无侵入规则验证', function () {

    // 被测表 + 关联表一次性声明（幂等）。表名与 fixture 模型一一对应。
    beforeAll(function () {
        defineTables([
            'spec_rule_article' => fn($t) => [$t->id(), $t->string('title')->nullable(), $t->tinyInteger('status')->default(1)],
            'spec_rule_node'    => fn($t) => [$t->id(), $t->string('name')->nullable()],
            'spec_rule_syslog'  => fn($t) => [$t->id(), $t->integer('userid')],
            'spec_rule_account' => fn($t) => [$t->id(), $t->string('name')->nullable()],
            'spec_rule_member'  => fn($t) => [$t->id(), $t->integer('account_id')],
        ]);
    });

    // 用例间清空数据，保证隔离
    beforeEach(function () {
        cleanTables(['spec_rule_article', 'spec_rule_node', 'spec_rule_syslog', 'spec_rule_account', 'spec_rule_member']);
        resetSqlBuffer();
    });

    describe('$rules + $validationMessages（增改验证）', function () {

        describe('新增（create）', function () {

            it('数据合法时验证通过并写入', function () {
                $ent = RuleArticle::create(['title' => 'abc', 'status' => 1]);

                expect($ent->exists)->toBe(true);
                expect($ent->getErrors()->isEmpty())->toBe(true);
                // 确认真正落库
                expect(Capsule::table('spec_rule_article')->count())->toBe(1);
            });

            it('违反 required 规则时验证失败、不写入、返回自定义消息', function () {
                $ent = RuleArticle::create(['title' => '', 'status' => 1]);

                // watson/validating：验证失败时 create 的 save 中止，模型未落库
                expect($ent->exists)->toBe(false);
                expect($ent->getErrors()->get('title')[0])->toBe('标题必填');
                expect(Capsule::table('spec_rule_article')->count())->toBe(0);
            });

            it('违反 max 规则时返回对应自定义消息', function () {
                $ent = RuleArticle::create(['title' => '超过五个字啦', 'status' => 1]);

                expect($ent->exists)->toBe(false);
                expect($ent->getErrors()->get('title')[0])->toBe('标题最长5字');
            });

            it('违反 in 规则时返回对应自定义消息', function () {
                $ent = RuleArticle::create(['title' => 'ok', 'status' => 9]);

                expect($ent->exists)->toBe(false);
                expect($ent->getErrors()->get('status')[0])->toBe('状态值非法');
            });
        });

        describe('修改（save）', function () {

            beforeEach(function () {
                // 先写入一条合法记录用于修改场景
                Capsule::table('spec_rule_article')->insert([
                    'id'     => 10,
                    'title'  => 'ok',
                    'status' => 1,
                ]);
            });

            it('改成非法值时验证失败、保留原值', function () {
                $ent = RuleArticle::find(10);
                $ent->status = 9; // 非法
                $result = $ent->save();

                expect($result)->toBe(false);
                expect($ent->getErrors()->get('status')[0])->toBe('状态值非法');
                // 原值未变
                $fresh = Capsule::table('spec_rule_article')->where('id', 10)->first();
                expect($fresh->status)->toBe(1);
            });

            it('改成合法值时验证通过并更新', function () {
                $ent = RuleArticle::find(10);
                $ent->title = 'new';
                $result = $ent->save();

                expect($result)->toBe(true);
                expect($ent->getErrors()->isEmpty())->toBe(true);
                $fresh = Capsule::table('spec_rule_article')->where('id', 10)->first();
                expect($fresh->title)->toBe('new');
            });
        });

        it('getRules / getValidationMessages 能取到子类配置', function () {
            $ent = new RuleArticle();
            expect($ent->getRules())->toBe([
                'title'  => 'required|max:5',
                'status' => 'required|in:0,1',
            ]);
            expect($ent->getValidationMessages()['title.required'])->toBe('标题必填');
        });
    });

    describe('$deleteRules + $deleteValidationMessages（删除验证）', function () {

        beforeEach(function () {
            Capsule::table('spec_rule_node')->insert([
                ['id' => 1, 'name' => 'root'],
                ['id' => 2, 'name' => 'child'],
                ['id' => 3, 'name' => 'leaf'],
            ]);
        });

        it('未配置删除规则时验证直接通过', function () {
            // RuleArticle 没有 deleteRules
            Capsule::table('spec_rule_article')->insert(['id' => 1, 'title' => 'x', 'status' => 1]);
            $ent = RuleArticle::find(1);
            expect($ent->validateBeforeDelete())->toBe(true);
            expect($ent->getErrors()->isEmpty())->toBe(true);
        });

        it('命中 not_in 规则时删除被阻止、记录保留、返回自定义消息', function () {
            $ent = RuleNode::find(1); // id=1 命中 not_in:1
            $result = $ent->delete();

            expect($result)->toBe(false);
            expect($ent->getErrors()->get('id')[0])->toBe('不能删除根节点');
            // 记录仍在
            expect(RuleNode::find(1))->not->toBeNull();
        });

        it('命中 no_related 规则时删除被阻止并返回自定义消息', function () {
            Capsule::table('spec_rule_syslog')->insert(['userid' => 2]);

            $ent = RuleNode::find(2);
            $result = $ent->delete();

            expect($result)->toBe(false);
            expect($ent->getErrors()->get('id')[0])->toBe('存在关联日志，禁止删除');
            expect(RuleNode::find(2))->not->toBeNull();
        });

        it('no_related 规则会发起 count 子查询并以本模型 id 为绑定值', function () {
            Capsule::table('spec_rule_syslog')->insert(['userid' => 3]);

            $ent = RuleNode::find(3);

            $sqls = captureSql(function () use ($ent) {
                $ent->validateBeforeDelete();
            });

            $countSql = $sqls[count($sqls) - 1];
            expect($countSql['sql'])->toContain('select count(*)');
            expect($countSql['sql'])->toContain('"spec_rule_syslog"');
            expect($countSql['sql'])->toContain('"userid" = ?');
            expect($countSql['bindings'])->toBe([3]);
        });

        it('所有删除规则都满足时删除成功', function () {
            // id=3，无 not_in 命中，且无关联日志
            $ent = RuleNode::find(3);
            $result = $ent->delete();

            expect($result)->toBe(true);
            expect($ent->getErrors()->isEmpty())->toBe(true);
            expect(RuleNode::find(3))->toBeNull();
        });

        it('getDeleteRules / getDeleteValidationMessages 能取到子类配置', function () {
            $ent = new RuleNode();
            expect($ent->getDeleteRules())->toBe(['id' => 'not_in:1|no_related:spec_rule_syslog,userid']);
            expect($ent->getDeleteValidationMessages()['id.not_in'])->toBe('不能删除根节点');
        });
    });

    describe('$deleteCascade（联动删除）', function () {

        beforeEach(function () {
            Capsule::table('spec_rule_account')->insert([
                ['id' => 1, 'name' => 'a1'],
                ['id' => 2, 'name' => 'a2'],
            ]);
            Capsule::table('spec_rule_member')->insert([
                ['account_id' => 1],
                ['account_id' => 1],
                ['account_id' => 2],
            ]);
        });

        it('删除主记录时一并清理关联表记录', function () {
            $ent = RuleAccount::find(1);
            $result = $ent->delete();

            expect($result)->toBe(true);
            // 主记录删除
            expect(RuleAccount::find(1))->toBeNull();
            // 关联记录被联动删除
            expect(Capsule::table('spec_rule_member')->where('account_id', 1)->count())->toBe(0);
            // 其它 account 的关联记录不受影响
            expect(Capsule::table('spec_rule_member')->where('account_id', 2)->count())->toBe(1);
        });

        it('级联删除会生成带外键条件的 delete 语句', function () {
            $ent = RuleAccount::find(2);

            $sqls = captureSql(function () use ($ent) {
                $ent->delete();
            });

            // 应包含一条针对关联表的 delete
            $cascadeSqls = array_filter($sqls, function ($s) {
                return stripos($s['sql'], 'delete from "spec_rule_member"') !== false;
            });
            expect($cascadeSqls)->not->toBeEmpty();

            $cascade = array_values($cascadeSqls)[0];
            expect($cascade['sql'])->toContain('"account_id" = ?');
            expect($cascade['bindings'])->toBe([2]);
        });

        it('getDeleteCascade 能取到子类配置', function () {
            $ent = new RuleAccount();
            expect($ent->getDeleteCascade())->toBe([
                ['table' => 'spec_rule_member', 'foreign_key' => 'account_id'],
            ]);
        });

        it('未配置 deleteCascade 时无连带删除副作用', function () {
            // RuleNode 未配置 deleteCascade，删除时不应触碰其它表
            Capsule::table('spec_rule_node')->insert(['id' => 1, 'name' => 'x']);

            $sqls = captureSql(function () {
                $ent = RuleNode::find(1);
                $ent->delete();
            });

            $anyCascade = array_filter($sqls, function ($s) {
                return stripos($s['sql'], 'delete from') !== false
                    && stripos($s['sql'], 'spec_rule_node') === false;
            });
            expect($anyCascade)->toBeEmpty();
        });
    });

    describe('删除验证与级联删除的协同顺序', function () {

        beforeEach(function () {
            Capsule::table('spec_rule_account')->insert(['id' => 5, 'name' => 'a5']);
            Capsule::table('spec_rule_member')->insert(['account_id' => 5]);
        });

        it('验证通过后才执行级联删除，且级联先于主表删除', function () {
            // 用一个既带 deleteCascade 又让它验证通过的模型删除
            $ent = RuleAccount::find(5);

            $sqls = captureSql(function () use ($ent) {
                $ent->delete();
            });

            // 找出关联表 delete 与主表 delete 的位置
            $positions = [];
            foreach ($sqls as $i => $s) {
                if (stripos($s['sql'], 'delete from "spec_rule_member"') !== false) {
                    $positions['cascade'] = $i;
                }
                if (stripos($s['sql'], 'delete from "spec_rule_account"') !== false) {
                    $positions['main'] = $i;
                }
            }
            expect($positions)->toContainKey('cascade');
            expect($positions)->toContainKey('main');
            expect($positions['cascade'])->toBeLessThan($positions['main']);
        });
    });
});
