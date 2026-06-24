<?php
/**
 * Fixture — 文章模型，用于测试增改验证（$rules / $validationMessages）。
 *
 * 表名：spec_rule_article（字段：id、title、status），由对应 spec 在内存库建立。
 * 命名空间 Specs\Support\FixtureModels，由 bootstrap.php 的自动加载器
 * （Specs\Support\* → lara/specs/Support/）按 PSR 规则解析。
 */

namespace Specs\Support\FixtureModels;

use Qscmf\Core\BaseModel;

class RuleArticle extends BaseModel
{
    protected $table = 'spec_rule_article';

    public $timestamps = false;

    protected $guarded = [];

    protected $rules = [
        'title'  => 'required|max:5',
        'status' => 'required|in:0,1',
    ];

    protected $validationMessages = [
        'title.required'  => '标题必填',
        'title.max'        => '标题最长5字',
        'status.required' => '状态必填',
        'status.in'        => '状态值非法',
    ];
}
