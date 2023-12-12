<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAreaForZhongshan extends Migration
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
        $area_data = [
            [
                'id' => '442001',
                'cname' => '城区',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442002',
                'cname' => '黄圃镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442003',
                'cname' => '东凤镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442004',
                'cname' => '古镇镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442005',
                'cname' => '沙溪镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442006',
                'cname' => '坦洲镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442007',
                'cname' => '港口镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442008',
                'cname' => '三角镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442009',
                'cname' => '横栏镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442010',
                'cname' => '南头镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442011',
                'cname' => '阜沙镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442012',
                'cname' => '三乡镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442013',
                'cname' => '板芙镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442014',
                'cname' => '大涌镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442015',
                'cname' => '神湾镇',
                'cname1' => '',
                'upid' => '442000',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '442016',
                'cname' => '小榄镇',
                'cname1' => '',
                'upid' => '442000',
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
        \Illuminate\Support\Facades\DB::table('qs_area')->where('upid', '=', 442000)->delete();
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
