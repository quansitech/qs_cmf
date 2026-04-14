<?php
namespace App\Models;

use \Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;

class Config extends Model{

    protected $table = 'config';

    public $timestamps = false;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->update_time = time();
        });
    }

    public function scopeLists($query)
    {
        return $query->where('status', DBCont::NORMAL_STATUS)
                     ->select('type', 'name', 'value');
    }

    public static function lists()
    {
        $data = self::where('status', DBCont::NORMAL_STATUS)
                     ->select('type', 'name', 'value')
                     ->get();

        $config = [];
        foreach ($data as $value) {
            $config[$value->name] = self::parseConfig($value->type, $value->value);
        }
        return $config;
    }

    public static function getLists()
    {
        return self::lists();
    }

    private static function parseConfig($type, $value)
    {
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

    public function scopeGetConfigList($query, $map){
        return $query->where($map)->orderBy('sort')->get();
    }

    public static function getConfigList($map){
        return self::query()->where($map)->orderBy('sort')->get()->toArray();
    }

    public static function updateConfig($name, $value){
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
