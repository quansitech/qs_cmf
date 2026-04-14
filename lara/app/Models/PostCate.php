<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;

class PostCate extends Model
{
    protected $table = 'post_cate';
    public $timestamps = false;
    protected $guarded = [];

    public static function getOne($id)
    {
        $ent = self::find($id);
        return $ent ? $ent->toArray() : null;
    }

    public static function createAdd($data)
    {
        $ent = self::create($data);
        return $ent ? $ent->id : false;
    }

    public static function createSave($data)
    {
        if (isset($data['id'])) {
            return self::where('id', $data['id'])->update($data);
        }
        return false;
    }

    public static function getOneField($id, $field)
    {
        return self::where('id', $id)->value($field);
    }

    /**
     * 获取父级分类选项（供下拉选择器使用）
     */
    public static function getParentOptions($id_key, $name_key, $exclude_id = null)
    {
        $query = self::where('status', DBCont::NORMAL_STATUS);
        if ($exclude_id) {
            $query->where('id', '!=', $exclude_id);
        }
        $list = $query->get()->toArray();
        $tree = list_to_tree($list);
        $options = genSelectByTree($tree);
        $result = [];
        foreach ($options as $item) {
            $prefix = str_repeat("&nbsp;", $item['level'] * 4);
            $prefix .= empty($prefix) ? '' : "┝ ";
            $result[$item[$id_key]] = $prefix . $item[$name_key];
        }
        return $result;
    }
}
