<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;

class Role extends Model
{
    protected $table = 'role';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * 验证规则
     */
    protected $rules = [
        'name' => 'required|string|max:20|unique:role,name',
        'status' => 'required|in:0,1',
        'pid' => 'integer',
    ];

    /**
     * 验证错误消息
     */
    protected $validationMessages = [
        'name.required' => '用户组名称是必填项',
        'name.unique' => '已存在用户组名称',
        'status.required' => '状态必填',
        'status.in' => '状态值超出范围',
    ];

    /**
     * 删除验证规则
     */
    protected $deleteRules = [
        'id' => 'no_related:access,role_id|no_related:role_user,role_id',
    ];

    /**
     * 删除验证错误消息
     */
    protected $deleteValidationMessages = [
        'id.no_related' => '请先清空用户组权限或用户数据',
    ];

    /**
     * 自动设置状态
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->status)) {
                $model->status = DBCont::NORMAL_STATUS;
            }
        });
    }

    /**
     * 获取角色列表（从 ThinkPHP 迁移）
     */
    public static function getOne($id){
        $role = self::find($id);
        return $role ? $role->toArray() : null;
    }

    public static function getListForCount($map = []){
        $query = self::query();

        if(isset($map['name'])){
            $query->where('name', $map['name']);
        }
        if(isset($map['status'])){
            $query->where('status', $map['status']);
        }

        return $query->count();
    }

    public static function getListForPage($map = [], $page = 1, $rows = 20, $order = 'status desc, id desc'){
        $query = self::query();

        if(isset($map['name'])){
            $query->where('name', $map['name']);
        }
        if(isset($map['status'])){
            $query->where('status', $map['status']);
        }

        $offset = ($page - 1) * $rows;
        return $query->orderByRaw($order)->offset($offset)->limit($rows)->get()->toArray();
    }

    public static function getRoleList($map = [])
    {
        $query = self::where('status', DBCont::NORMAL_STATUS);

        if (!empty($map)) {
            foreach ($map as $key => $value) {
                $query->where($key, $value);
            }
        }

        return $query->get()->toArray();
    }
}
