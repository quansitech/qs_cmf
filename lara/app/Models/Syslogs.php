<?php
namespace App\Models;

use \Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;

class Syslogs extends Model{

    protected $table = 'syslogs';
    public $timestamps = false;
    protected $guarded = [];

}
