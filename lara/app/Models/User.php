<?php
namespace App\Models;

use Gy_Library\DBCont;
use Qscmf\Core\BaseModel;

class User extends BaseModel{

    protected $table = 'user';
    protected $guarded = [];

    public $timestamps = false;
    public $error = "";

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->register_date)) {
                $model->register_date = time();
            }
        });
    }

    /**
     * 验证规则（从 ThinkPHP UserModel 迁移）
     */
    protected $rules = [
        'nick_name' => 'required|string|max:255|unique:user,nick_name',
        'status' => 'required|in:0,1',
        'pwd' => 'required',
        'telephone' => 'required|regex:/^1\d{10}$/',
        'email' => 'required|email|max:255',
    ];

    /**
     * 验证错误消息
     */
    protected $validationMessages = [
        'nick_name.required' => '请填写用户名',
        'nick_name.unique' => '用户名已存在',
        'status.required' => '必选填写用户状态',
        'status.in' => '状态值超出范围',
        'pwd.required' => '密码必填',
        'pwd.min' => '密码长度必须在6-12位',
        'pwd.max' => '密码长度必须在6-12位',
        'telephone.required' => '手机号码必填',
        'telephone.regex' => '手机号码格式不正确',
        'email.required' => '电子邮箱必填',
        'email.email' => '邮箱格式不正确',
    ];

    /**
     * 删除验证规则（类似 ThinkPHP 的 $_delete_validate）
     *
     * 可用规则：
     * - not_in:1,2,3 - ID 不能在指定值中
     * - no_related:表名,外键 - 关联表中不能存在记录
     */
    protected $deleteRules = [
        'id' => 'not_in:1|no_related:syslogs,userid',  // 多个规则用 | 分隔
    ];

    /**
     * 删除验证错误消息
     */
    protected $deleteValidationMessages = [
        'id.not_in' => '不能删除超级管理员账户',
        'id.no_related' => '已经产生了系统日记，该用户只能禁用，不能删除',
    ];

    /**
     * 删除时自动删除的关联表（类似 ThinkPHP 的 $_delete_auto）
     */
    protected $deleteCascade = [
        ['table' => 'role_user', 'foreign_key' => 'user_id'],  // 删除用户时，同时删除角色关联
    ];

    //后台登录
    public function adminLogin($login_name, $pwd) {
        //当用户上传大文件时，时间会非常长，为了方便其有充足的上传时间，而设置了一天的过期时间
        $data = $this->getUserByEmailOrNickName($login_name);
        if(!$data){
            $this->error = '用户不存在';
            return false;
        }
        $r = $this->_login($data['id'], $pwd);
         if($r === false){
             return false;
         }
         session('ADMIN_LOGIN', true);
         session('HOME_LOGIN', null);
         sysLogs('后台登录');
         return true;
    }

    public function getUserByEmailOrNickName($email_or_nickName){
        if(filter_var($email_or_nickName, FILTER_VALIDATE_EMAIL) === false){
            $map['nick_name'] = $email_or_nickName;
        }
        else{
            $map['email'] = $email_or_nickName;
        }

        $user_ent = $this->where($map)->first();
        
        return $user_ent ? $user_ent->toArray() : null;
                
    }

    //进一步分解_login，已满足一键登录功能
    private function _login($id, $pwd){
        $user = $this->where('id', $id)->first();

        if (!$user) {
            E('model \'s data property is null!');
        }

        //密码验证
        if (!$this->verifyAndUpgradePwd($pwd, $user)) {
            $this->error = '密码错误';
            return false;
        }

        //非正常状态禁止登录
        if($user->status != DBCont::NORMAL_STATUS){
            $this->error = '用户被禁用';
            return false;
        }

        $user->last_login_time = time();
        $user->last_login_ip = get_client_ip();

        $user->save();

        cleanRbacKey();

        if (!C('USER_AUTH_ADMINID')) {
            E('C("USER_AUTH_ADMINID") is null');
        }

        session(C('USER_AUTH_KEY'), $user->id);

        //设置超级管理员权限
        if ($user->id == C('USER_AUTH_ADMINID')) {

            session(C('ADMIN_AUTH_KEY'), true);
        } else {
            session(C('ADMIN_AUTH_KEY'), false);
        }

        return true;
    }

    /**
     * 密码加密 - 使用 password_hash
     * @param string $ori_pwd 原始密码
     * @return string 密码hash
     */
    public function hashPwd($ori_pwd){
        return password_hash($ori_pwd, PASSWORD_DEFAULT);
    }

    /**
     * 密码验证 - 双重验证策略
     * 优先使用 bcrypt (password_verify)，回退到旧版 md5+salt 验证并自动升级
     * @param string $ori_pwd 原始密码
     * @param string $hash 密码hash
     * @param string|null $salt 旧版密码salt（md5+salt时使用）
     * @return bool
     */
    public function verifyPwd($ori_pwd, $hash, $salt = null){
        // 优先尝试 bcrypt 验证
        if (password_verify($ori_pwd, $hash)) {
            return true;
        }

        // 回退到旧版 md5+salt 验证
        if ($salt !== null) {
            $old_hash = md5(md5($ori_pwd) . $salt);
            if ($old_hash === $hash) {
                return true;
            }
        }

        return false;
    }

    /**
     * 验证密码并自动升级（用于 _login 流程）
     * @param string $ori_pwd 原始密码
     * @param object $user 用户对象（需要包含 pwd 和 salt 字段）
     * @return bool
     */
    public function verifyAndUpgradePwd($ori_pwd, $user){
        // 优先尝试 bcrypt 验证
        if (password_verify($ori_pwd, $user->pwd)) {
            return true;
        }

        // 回退到旧版 md5+salt 验证
        if (!empty($user->salt)) {
            $old_hash = md5(md5($ori_pwd) . $user->salt);
            if ($old_hash === $user->pwd) {
                // 自动升级为 bcrypt
                $user->pwd = password_hash($ori_pwd, PASSWORD_DEFAULT);
                $user->salt = null;
                $user->save();
                return true;
            }
        }

        return false;
    }

    /**
     * 等价于 GyListModel::getOne()
     */
    public static function getOne($id)
    {
        $ent = self::find($id);
        return $ent ? $ent->toArray() : null;
    }

    /**
     * 新增用户 - 使用 bcrypt 加密密码
     */
    public static function newUser($data)
    {
        if (strlen($data['pwd']) < 6 || strlen($data['pwd']) > 12) {
            return false;
        }
        $data['pwd'] = password_hash($data['pwd'], PASSWORD_DEFAULT);

        $only = ['nick_name', 'pwd', 'email', 'telephone', 'register_date', 'status', 'last_login_time', 'last_login_ip'];
        $data = array_intersect_key($data, $only);

        $ent = self::create($data);
        return $ent->wasRecentlyCreated ? $ent->id : false;
    }

    /**
     * 管理员修改密码 - 使用 bcrypt
     */
    public static function modifyPwdByAdmin($uid, $pwd)
    {
        if (strlen($pwd) < 6 || strlen($pwd) > 12) {
            return false;
        }
        return self::where('id', $uid)->update([
            'pwd' => password_hash($pwd, PASSWORD_DEFAULT),
        ]) !== false;
    }

    /**
     * 修改密码 - 使用 bcrypt
     */
    public static function modifyPwd($uid, $pwd)
    {
        if (strlen($pwd) < 6 || strlen($pwd) > 12) {
            return false;
        }
        return self::where('id', $uid)->update([
            'pwd' => password_hash($pwd, PASSWORD_DEFAULT),
        ]) !== false;
    }

    /**
     * 等价于 GyListModel::createAdd()
     */
    public static function createAdd($data)
    {
        $ent = self::create($data);
        return $ent->wasRecentlyCreated ? $ent->id : false;
    }

    /**
     * 等价于 GyListModel::createSave()
     */
    public static function createSave($data)
    {
        if (isset($data['id'])) {
            return self::where('id', $data['id'])->update($data);
        }
        return false;
    }

    /**
     * 等价于 GyListModel::getListForCount()
     */
    public static function getListForCount($map = [])
    {
        $query = self::query();
        buildQueryFromMap($query, $map);
        return $query->count();
    }

    /**
     * 等价于 GyListModel::getListForPage()
     */
    public static function getListForPage($map = [], $page = 1, $rows = 20, $order = 'id desc')
    {
        $query = self::query();
        buildQueryFromMap($query, $map);
        $offset = ($page - 1) * $rows;
        return $query->orderByRaw($order)->offset($offset)->limit($rows)->get()->toArray();
    }

    /**
     * 等价于 GyListModel::getList()
     */
    public static function getList($map = [], $order = 'id desc')
    {
        $query = self::query();
        buildQueryFromMap($query, $map);
        return $query->orderByRaw($order)->get()->toArray();
    }
}