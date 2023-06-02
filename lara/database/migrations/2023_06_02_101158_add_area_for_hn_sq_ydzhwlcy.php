<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAreaForHnSqYdzhwlcy extends Migration
{

    public function beforeCmmUp()
    {
        //
    }

    public function beforeCmmDown()
    {
        //
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $area_data = [
            [
                'id' => '411471',
                'cname' => '豫东综合物流产业聚集区',
                'cname1' => '',
                'upid' => '411400',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '411472',
                'cname' => '河南商丘经济开发区',
                'cname1' => '',
                'upid' => '411400',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
        ];
        \Illuminate\Support\Facades\DB::table('qs_area')->insert($area_data);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        \Illuminate\Support\Facades\DB::table('qs_area')->where('id', '=', 411471)->delete();
        \Illuminate\Support\Facades\DB::table('qs_area')->where('id', '=', 411472)->delete();

    }

    public function afterCmmUp()
    {
        //
    }

    public function afterCmmDown()
    {
        //
    }
}
