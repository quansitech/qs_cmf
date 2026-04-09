<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    protected $table = 'role_user';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * role_user 是中间表，不需要主键自增
     */
    public $incrementing = false;

    /**
     * 中间表通常不需要验证规则
     */
    protected $rules = [];

    /**
     * 根据用户ID获取角色ID
     */
    public static function getRoleIdsByUserId($user_id)
    {
        return self::where('user_id', $user_id)
            ->pluck('role_id')
            ->toArray();
    }

    /**
     * 根据角色ID获取用户ID列表
     */
    public static function getUserIdsByRoleId($role_id)
    {
        return self::where('role_id', $role_id)
            ->pluck('user_id')
            ->toArray();
    }

    public static function createAll($data){
        return self::insert($data);
    }
}
