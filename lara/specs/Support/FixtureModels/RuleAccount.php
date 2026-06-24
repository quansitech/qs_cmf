<?php
/**
 * Fixture — 级联删除模型，用于测试 deleteCascade 联动删除。
 *
 * 表名：spec_rule_account（字段：id、name）；关联表：spec_rule_member(account_id)。
 * 删除主记录时，一并清理 spec_rule_member 中 account_id 匹配的记录。
 * 由对应 spec 在内存库建立。
 */

namespace Specs\Support\FixtureModels;

use Qscmf\Core\BaseModel;

class RuleAccount extends BaseModel
{
    protected $table = 'spec_rule_account';

    public $timestamps = false;

    protected $guarded = [];

    protected $deleteCascade = [
        ['table' => 'spec_rule_member', 'foreign_key' => 'account_id'],
    ];
}
