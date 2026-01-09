<?php
namespace app\Models;

use \Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;

class Config extends Model{

    protected $table = 'qs_config';

    public $timestamps = false;

    public function scopeLists($query)
    {
        return $query->where('status', DBCont::NORMAL_STATUS)
                     ->select('type', 'name', 'value');
    }

    public function scopeGetConfigList($query, $map){
        return $query->where($map)->orderBy('sort')->get();
    }

    public function updateConfig($name, $value){
        $r = self::where('name', $name)->update(['value' => $value]);
        if($r){
            sysLogs('修改配置|'. $name . '=' . $value);
        }
        return $r;
    }

    /**
     * 根据配置类型解析配置
     * @param  integer $type  配置类型
     * @param  string  $value 配置值
     */
    private function _parse($type, $value){
        switch ($type) {
            case 'array':
                $re_value = parse_config_attr($value);
                break;
            default:
                $re_value = $value;
                break;
        }
        return $re_value;
    }
}
