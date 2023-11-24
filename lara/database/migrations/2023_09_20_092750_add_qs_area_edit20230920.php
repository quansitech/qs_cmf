<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQsAreaEdit20230920 extends Migration
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
        //贵州省 毕节市 黔西县
        \Illuminate\Support\Facades\DB::table('qs_area')->where(['id'=>'520522','upid'=>'520500'])->update(['cname'=>'黔西市','id'=>'520581']);        
        \Illuminate\Support\Facades\DB::table('qs_area')->where('upid', '=', 520522)->delete();
        $area_data = [
            [
                'id' => '520581001',
                'cname' => '水西街道',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581002',
                'cname' => '莲城街道',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581003',
                'cname' => '文峰街道',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581004',
                'cname' => '杜鹃街道',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581005',
                'cname' => '锦绣街道',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581100',
                'cname' => '金碧镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581101',
                'cname' => '雨朵镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581102',
                'cname' => '大关镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581103',
                'cname' => '谷里镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581104',
                'cname' => '素朴镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581105',
                'cname' => '中坪镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581106',
                'cname' => '重新镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581107',
                'cname' => '林泉镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],            
            [
                'id' => '520581108',
                'cname' => '金兰镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581109',
                'cname' => '锦星镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581110',
                'cname' => '洪水镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581111',
                'cname' => '甘棠镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581112',
                'cname' => '钟山镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581113',
                'cname' => '协和镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581114',
                'cname' => '观音洞镇',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581200',
                'cname' => '五里布依族苗族乡',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581201',
                'cname' => '绿化白族彝族乡',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581202',
                'cname' => '新仁苗族乡',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581203',
                'cname' => '铁石苗族彝族乡',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581204',
                'cname' => '太来彝族苗族乡',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581205',
                'cname' => '永燊彝族苗族乡',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581206',
                'cname' => '中建苗族彝族乡',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581207',
                'cname' => '花溪彝族苗族乡',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581208',
                'cname' => '定新彝族苗族乡',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581209',
                'cname' => '金坡苗族彝族满族乡',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581210',
                'cname' => '仁和彝族苗族乡',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520581211',
                'cname' => '红林彝族苗族乡',
                'cname1' => '',
                'upid' => '520581',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ]
        ];
        //删除莱芜市信息
        \Illuminate\Support\Facades\DB::table('qs_area')->insert($area_data);
        \Illuminate\Support\Facades\DB::table('qs_area')->where('upid', '=', 371200)->delete();     
        Illuminate\Support\Facades\DB::table('qs_area')->where('upid', '=', 371202)->delete();    
        Illuminate\Support\Facades\DB::table('qs_area')->where('upid', '=', 371203)->delete();       
        \Illuminate\Support\Facades\DB::table('qs_area')->where('id', '=', 371200)->delete();
        
        //山东省 济南市
        $areadata = [
            [
                'id' => '370116',
                'cname' => '莱芜区',
                'cname1' => '',
                'upid' => '370100',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '370116001',
                'cname' => '凤城街道',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370116002',
                'cname' => '张家洼街道',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370116003',
                'cname' => '高庄街道',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370116004',
                'cname' => '鹏泉街道',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370116005',
                'cname' => '口镇街道',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '370116006',
                'cname' => '羊里街道',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370116007',
                'cname' => '方下街道',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370116008',
                'cname' => '雪野街道',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370116103',
                'cname' => '牛泉镇',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370116104',
                'cname' => '苗山镇',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370116106',
                'cname' => '大王庄镇',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370116107',
                'cname' => '寨里镇',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	            	
            [
                'id' => '370116108',
                'cname' => '杨庄镇',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370116109',
                'cname' => '茶业口镇',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370116110',
                'cname' => '和庄镇',
                'cname1' => '',
                'upid' => '370116',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370117',
                'cname' => '钢城区',
                'cname1' => '',
                'upid' => '370100',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],
            [
                'id' => '370117001',
                'cname' => '艾山街道',
                'cname1' => '',
                'upid' => '370117',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370117002',
                'cname' => '里辛街道',
                'cname1' => '',
                'upid' => '370117',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370117003',
                'cname' => '汶源街道',
                'cname1' => '',
                'upid' => '370117',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370117004',
                'cname' => '颜庄街道',
                'cname1' => '',
                'upid' => '370117',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370117005',
                'cname' => '辛庄街道',
                'cname1' => '',
                'upid' => '370117',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370117400',
                'cname' => '棋山国家森林公园',
                'cname1' => '',
                'upid' => '370117',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '370117401',
                'cname' => '南部新城建设服务中心',
                'cname1' => '',
                'upid' => '370117',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ]	
        ];
        \Illuminate\Support\Facades\DB::table('qs_area')->insert($areadata);
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //贵州省 毕节市 黔西县
        \Illuminate\Support\Facades\DB::table('qs_area')->where(['id'=>'520581','upid'=>'520500'])->update(['cname'=>'黔西县','id'=>'520522']);
        \Illuminate\Support\Facades\DB::table('qs_area')->where('upid', '=', '520581')->delete();
        $area_data = [
            [
                'id' => '520522001',
                'cname' => '莲城街道',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522002',
                'cname' => '水西街道',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522003',
                'cname' => '文峰街道',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522004',
                'cname' => '杜鹃街道',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522101',
                'cname' => '金碧镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522102',
                'cname' => '雨朵镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522103',
                'cname' => '大关镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522104',
                'cname' => '谷里镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522105',
                'cname' => '素朴镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522106',
                'cname' => '中坪镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522107',
                'cname' => '重新镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522108',
                'cname' => '林泉镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522109',
                'cname' => '金兰镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522110',
                'cname' => '甘棠镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522111',
                'cname' => '洪水镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522112',
                'cname' => '锦星镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522113',
                'cname' => '钟山镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522114',
                'cname' => '协和镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522115',
                'cname' => '观音洞镇',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522200',
                'cname' => '五里布依族苗族乡',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522203',
                'cname' => '绿化白族彝族乡',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522204',
                'cname' => '新仁苗族乡',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522206',
                'cname' => '铁石苗族彝族乡',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522208',
                'cname' => '太来彝族苗族乡',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522210',
                'cname' => '永燊彝族苗族乡',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522211',
                'cname' => '中建苗族彝族乡',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522212',
                'cname' => '花溪彝族苗族乡',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522213',
                'cname' => '定新彝族苗族乡',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522215',
                'cname' => '金坡苗族彝族满族乡',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522216',
                'cname' => '仁和彝族苗族乡',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],
            [
                'id' => '520522217',
                'cname' => '红林彝族苗族乡',
                'cname1' => '',
                'upid' => '520522',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ]
        ];        
        \Illuminate\Support\Facades\DB::table('qs_area')->insert($area_data);
        //添加莱芜市信息
        \Illuminate\Support\Facades\DB::table('qs_area')->where('id', '=', '370116')->delete();
        \Illuminate\Support\Facades\DB::table('qs_area')->where('upid', '=', '370116')->delete();
        \Illuminate\Support\Facades\DB::table('qs_area')->where('id', '=', '370117')->delete();
        \Illuminate\Support\Facades\DB::table('qs_area')->where('upid', '=', '370117')->delete();
        $areadata = [
            [
                'id' => '371200',
                'cname' => '莱芜市',
                'cname1' => '莱芜',
                'upid' => '370000',
                'ename' => '',
                'pinyin' => '',
                'level' => '2'
            ],	
            [
                'id' => '371202',
                'cname' => '莱城区',
                'cname1' => '',
                'upid' => '371200',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],	
            [
                'id' => '371203',
                'cname' => '钢城区',
                'cname1' => '',
                'upid' => '371200',
                'ename' => '',
                'pinyin' => '',
                'level' => '3'
            ],	
            [
                'id' => '371202001',
                'cname' => '凤城街道',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202002',
                'cname' => '张家洼街道',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202003',
                'cname' => '高庄街道',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202004',
                'cname' => '鹏泉街道',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202100',
                'cname' => '口镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202101',
                'cname' => '羊里镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202102',
                'cname' => '方下镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202103',
                'cname' => '牛泉镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202105',
                'cname' => '苗山镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202106',
                'cname' => '雪野镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202107',
                'cname' => '大王庄镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202108',
                'cname' => '寨里镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202109',
                'cname' => '杨庄镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202110',
                'cname' => '茶业口镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371202111',
                'cname' => '和庄镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371203001',
                'cname' => '艾山街道',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371203002',
                'cname' => '里辛街道',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371203003',
                'cname' => '汶源街道',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371203100',
                'cname' => '颜庄镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ],	
            [
                'id' => '371203103',
                'cname' => '辛庄镇',
                'cname1' => '',
                'upid' => '371202',
                'ename' => '',
                'pinyin' => '',
                'level' => '4'
            ]
        ];        
        \Illuminate\Support\Facades\DB::table('qs_area')->insert($areadata);
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
