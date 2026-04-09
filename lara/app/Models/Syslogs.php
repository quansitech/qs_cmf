<?php
namespace App\Models;

use \Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;

class Syslogs extends Model{

    protected $table = 'syslogs';
    public $timestamps = false;
    protected $guarded = [];

    public static function getListForCount($map = []){
        $query = self::query();

        if(isset($map['userid'])){
            $query->where('userid', $map['userid']);
        }
        if(isset($map['modulename'])){
            $query->where('modulename', $map['modulename']);
        }
        if(isset($map['actionname'])){
            $query->where('actionname', $map['actionname']);
        }
        if(isset($map['message'])){
            $query->where('message', 'like', '%' . $map['message'] . '%');
        }

        return $query->count();
    }

    public static function getListForPage($map = [], $page = 1, $rows = 20, $order = 'create_time desc'){
        $query = self::query();

        if(isset($map['userid'])){
            $query->where('userid', $map['userid']);
        }
        if(isset($map['modulename'])){
            $query->where('modulename', $map['modulename']);
        }
        if(isset($map['actionname'])){
            $query->where('actionname', $map['actionname']);
        }
        if(isset($map['message'])){
            $query->where('message', 'like', '%' . $map['message'] . '%');
        }

        $offset = ($page - 1) * $rows;
        return $query->orderByRaw($order)->offset($offset)->limit($rows)->get()->toArray();
    }

    public static function getList($map = [], $order = 'create_time desc'){
        $query = self::query();

        if(isset($map['userid'])){
            $query->where('userid', $map['userid']);
        }
        if(isset($map['modulename'])){
            $query->where('modulename', $map['modulename']);
        }
        if(isset($map['actionname'])){
            $query->where('actionname', $map['actionname']);
        }
        if(isset($map['message'])){
            $query->where('message', 'like', '%' . $map['message'] . '%');
        }

        return $query->orderByRaw($order)->get()->toArray();
    }
}
