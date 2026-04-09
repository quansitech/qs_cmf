<?php
namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Access extends Model{

    protected $table = 'access';
    public $timestamps = false;
    protected $guarded = [];

    public static function delAccess($map){
        $query = self::query();

        if(isset($map['role_id'])){
            $query->where('role_id', $map['role_id']);
        }
        if(isset($map['node_id'])){
            $query->where('node_id', $map['node_id']);
        }
        if(isset($map['level'])){
            $query->where('level', $map['level']);
        }

        return $query->delete();
    }

    public static function getAccessList($map){
        $query = self::query();

        if(isset($map['role_id'])){
            $query->where('role_id', $map['role_id']);
        }
        if(isset($map['node_id'])){
            $query->where('node_id', $map['node_id']);
        }
        if(isset($map['level'])){
            $query->where('level', $map['level']);
        }

        return $query->get()->toArray();
    }

    public static function createAll($data){
        return self::insert($data);
    }
}
