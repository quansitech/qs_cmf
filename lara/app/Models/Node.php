<?php
namespace App\Models;

use \Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;

class Node extends Model{

    protected $table = 'node';
    public $timestamps = false;
    protected $fillable = ['name', 'title', 'status', 'remark', 'sort', 'pid', 'level', 'menu_id', 'icon', 'url'];

    /**
     * Find a node by conditions
     * @param array $map Conditions array
     * @return array|null Node data as array or null if not found
     */
    public static function getNode($map){
        $query = self::query();

        if(isset($map['id'])){
            $query = $query->where('id', $map['id']);
        }
        if(isset($map['name'])){
            $query = $query->where('name', $map['name']);
        }
        if(isset($map['status'])){
            $query = $query->where('status', $map['status']);
        }
        if(isset($map['level'])){
            $query = $query->where('level', $map['level']);
        }
        if(isset($map['pid'])){
            $query = $query->where('pid', $map['pid']);
        }

        $node = $query->first();

        return $node ? $node->toArray() : null;
    }

    public static function getOne($id){
        $node = self::find($id);
        return $node ? $node->toArray() : null;
    }

    public static function getListForCount($map){
        $query = self::query();

        if(isset($map['name'])){
            $query->where('name', $map['name']);
        }
        if(isset($map['status'])){
            $query->where('status', $map['status']);
        }
        if(isset($map['level'])){
            $query->where('level', $map['level']);
        }
        if(isset($map['pid'])){
            $query->where('pid', $map['pid']);
        }

        return $query->count();
    }

    public static function getListForPage($map, $page, $rows, $order = 'id desc'){
        $query = self::query();

        if(isset($map['name'])){
            $query->where('name', $map['name']);
        }
        if(isset($map['status'])){
            $query->where('status', $map['status']);
        }
        if(isset($map['level'])){
            $query->where('level', $map['level']);
        }
        if(isset($map['pid'])){
            $query->where('pid', $map['pid']);
        }

        $offset = ($page - 1) * $rows;
        return $query->orderByRaw($order)->offset($offset)->limit($rows)->get()->toArray();
    }

    public static function getModuleList(){
        return self::where('level', DBCont::LEVEL_MODULE)
                  ->where('status', DBCont::NORMAL_STATUS)
                  ->orderBy('sort', 'asc')
                  ->get()
                  ->toArray();
    }

    public static function getNodeList($map){
        $query = self::query();

        if(isset($map['name'])){
            $query->where('name', $map['name']);
        }
        if(isset($map['status'])){
            $query->where('status', $map['status']);
        }
        if(isset($map['level'])){
            $query->where('level', $map['level']);
        }
        if(isset($map['pid'])){
            $query->where('pid', $map['pid']);
        }

        return $query->orderBy('sort', 'asc')->get()->toArray();
    }

    /**
     * 获取节点名称
     */
    public static function getNodeName($module, $controller, $action)
    {
        $module_ent = self::where('name', $module)->where('level', DBCont::LEVEL_MODULE)->first();
        if (!$module_ent) return '';

        $controller_ent = self::where('name', $controller)->where('level', DBCont::LEVEL_CONTROLLER)->where('pid', $module_ent->id)->first();
        if (!$controller_ent) return '';

        $node_ent = self::where('name', $action)->where('level', DBCont::LEVEL_ACTION)->where('pid', $controller_ent->id)->first();
        return $node_ent ? $node_ent->title : '';
    }

    /**
     * 检查节点是否存在（供 AuthCheckWidget 和 function.php 使用）
     */
    public static function isExistsNode($module, $controller, $action)
    {
        $module_node = self::where('name', $module)->where('level', 1)->first();
        if (!$module_node) return false;

        $controller_node = self::where('name', $controller)->where('level', 2)->where('pid', $module_node->id)->first();
        if (!$controller_node) return false;

        $action_node = self::where('name', $action)->where('level', 3)->where('pid', $controller_node->id)->first();
        return $action_node ? true : false;
    }

}