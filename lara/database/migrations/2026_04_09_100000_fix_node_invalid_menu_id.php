<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class FixNodeInvalidMenuId extends Migration
{
    /**
     * Run the migrations.
     * 将引用了不存在菜单的节点的 menu_id 重置为 0
     *
     * @return void
     */
    public function up()
    {
        $prefix = DB::getTablePrefix();

        DB::statement("UPDATE {$prefix}node SET menu_id = 0 WHERE menu_id != 0 AND menu_id NOT IN (SELECT id FROM {$prefix}menu)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
