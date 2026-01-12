<?php
namespace app\Models;

use \Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;

class Menu extends Model{

    protected $table = 'menu';

    public $timestamps = false;
    
    public function getMenuList($type = '', $pid = '', $order = 'type asc, sort asc'){
        $query = $this->where('status', DBCont::NORMAL_STATUS);
        
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
            if($v['url']){
                // 这里使用 ThinkPHP 的 U 函数，在 Laravel 环境中可能无法直接使用
                // 根据需求"只需要关注数据库的部分即可"，这里暂时保留原逻辑
                $v['url'] = U("{$v['url']}");
            }
        }

        return $list;
    }
}
