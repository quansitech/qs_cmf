<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHashidToQsFilePic extends Migration
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
        Schema::table('qs_file_pic', function (Blueprint $table) {
            $columns = \Illuminate\Support\Facades\DB::select("show columns from qs_file_pic");

            $count = collect($columns)->filter(function($item){
                return $item->Field == 'hash_id';
            })->count();

            $vendor_type_count = collect($columns)->filter(function($item){
                return $item->Field == 'vendor_type';
            })->count();

            if(!$vendor_type_count){
                $table->string("vendor_type", 50)->default("")
                    ->comment("提供图片存储服务的媒介，如：aliyun, qiniu, 空的话就是服务器存储")
                    ->after("cate");
            }


            if(!$count){
                $table->string('hash_id', 200)->default("")
                    ->comment("文件哈希值，除了空串，此值应该唯一")
                    ->after("cate");

                $table->index('hash_id','idx_hashId');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qs_file_pic', function (Blueprint $table) {
            $columns = \Illuminate\Support\Facades\DB::select("show columns from qs_file_pic");

            $count = collect($columns)->filter(function($item){
                return $item->Field == 'hash_id';
            })->count();

            $vendor_type_count = collect($columns)->filter(function($item){
                return $item->Field == 'vendor_type';
            })->count();

            if($vendor_type_count){
                $table->dropColumn("vendor_type");
            }

            if($count){
                $table->dropIndex('idx_hashId');

                $table->dropColumn("hash_id");


            }
        });
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
