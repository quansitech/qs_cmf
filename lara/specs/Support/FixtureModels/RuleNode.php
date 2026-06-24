<?php
/**
 * Fixture — 删除受限模型，用于测试删除验证（$deleteRules / $deleteValidationMessages）。
 *
 * 表名：spec_rule_node（字段：id、name）；关联表：spec_rule_syslog(userid)。
 * 由对应 spec 在内存库建立。
 */

namespace Specs\Support\FixtureModels;

use Qscmf\Core\BaseModel;

class RuleNode extends BaseModel
{
    protected $table = 'spec_rule_node';

    public $timestamps = false;

    protected $guarded = [];

    // 没有 $rules —— 增改时不验证

    protected $deleteRules = [
        'id' => 'not_in:1|no_related:spec_rule_syslog,userid',
    ];

    protected $deleteValidationMessages = [
        'id.not_in'    => '不能删除根节点',
        'id.no_related' => '存在关联日志，禁止删除',
    ];
}
