<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;

class Post extends Model
{
    protected $table = 'post';
    public $timestamps = false;
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        // MODEL_BOTH: publish_date 在创建和更新时都做 strtotime
        static::saving(function ($model) {
            if (isset($model->publish_date) && is_string($model->publish_date) && !is_numeric($model->publish_date)) {
                $model->publish_date = strtotime($model->publish_date);
            }
        });
    }

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
}
