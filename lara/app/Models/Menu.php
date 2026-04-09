<?php
namespace App\Models;

use \Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;

class Menu extends Model{

    protected $table = 'menu';

    public $timestamps = false;

    protected $guarded = [];

    public static function getOne($id){
        $menu = self::find($id);
        return $menu ? $menu->toArray() : null;
    }

    public static function getParentOptions($key = 'id', $value = 'title', $exclude_id = '', $prefix = '┝ '){
        $query = self::where('status', DBCont::NORMAL_STATUS);
        if($exclude_id !== ''){
            $query->where('id', '!=', $exclude_id);
        }
        $list = $query->get()->toArray();
        $tree = list_to_tree($list);
        $select = genSelectByTree($tree);
        $options = [];
        foreach($select as $v){
            $title_prefix = str_repeat("&nbsp;", $v['level'] * 4);
            $title_prefix .= empty($title_prefix) ? '' : $prefix;
            $options[$v[$key]] = $title_prefix . $v[$value];
        }
        return $options;
    }

    
    public static function getMenuList($type = '', $pid = '', $order = 'type asc, sort asc'){
        $query = self::where('status', DBCont::NORMAL_STATUS);
        
        if($type != ''){
            $query->where('type', $type);
        }
        if($pid != ''){
            $query->where('pid', $pid);
        }
        
        // 处理排序
        $query->orderByRaw($order);
        
        $list = $query->get()->toArray();
        
        // 处理URL - 保持与原来相同的逻辑
        foreach ($list as &$v){
            if($v['url'] && function_exists('U')){
                $v['url'] = U("{$v['url']}");
            }
        }

        return $list;
    }

    public static function getMenuListGroupByType(){
        $menu_list = self::getMenuList();
        $r = [];
        foreach ($menu_list as $v){
            $r[$v['type']][] = ['id' => $v['id'], 'title' => $v['title']];
        }
        return $r;
    }
}
