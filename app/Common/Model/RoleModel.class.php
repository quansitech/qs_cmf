<?php
namespace Common\Model;
use Gy_Library\DBCont;

class RoleModel extends \Gy_Library\GyListModel{
    
    protected $model_name = '用户组';
    
    protected $_validate = array(
        array('name', 'require', '用户组名称是必填项'),
        array('name', '', '已存在用户组名称', parent::MUST_VALIDATE, 'unique', parent::MODEL_BOTH),
        array('status',array(DBCont::FORBIDDEN_STATUS, DBCont::NORMAL_STATUS),'{%STATUS_OUT_OF_RANGE}',parent::MUST_VALIDATE, in, parent::MODEL_BOTH),
    );
    
    protected $_auto = array(
        array('status', DBCont::NORMAL_STATUS, self::MODEL_INSERT),
    );
    
    protected $_delete_validate = array(
        array('Access', 'role_id', parent::EXIST_VALIDATE , '请先清空用户组权限'),
        array('RoleUser', 'role_id', parent::EXIST_VALIDATE , '请先清空用户组下面的用户数据'),
    );
    
    
    public function getRoleList($map = array()){
        $map['status'] = DBCont::NORMAL_STATUS;
        return $this->where($map)->select();
    }
    
    
    
    public function getUserIdsByRoleName($role_name){
        $map['name'] = array('like', '%' . $role_name . '%');
        return $this->join('__ROLE_USER__ ON __ROLE__.id=__ROLE_USER__.role_id')
                ->where($map)->getField('user_id', true);
    }
    
    public function getRoleByUserId($user_id){
        $role_user_ent = M('RoleUser')->where(array('user_id' => $user_id))->find();
        $role_ent = $this->where(array('status' => DBCont::NORMAL_STATUS, 'id' => $role_user_ent['role_id']))->find();
        if(!$role_ent){
            E('无法获取角色数据');
        }
        return $role_ent;
    }
}
