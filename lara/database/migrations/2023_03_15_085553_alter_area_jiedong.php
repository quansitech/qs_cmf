<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAreaJiedong extends Migration
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
        $tablePrefix = DB::getTablePrefix();
        DB::unprepared("UPDATE {$tablePrefix}area SET cname = '揭东区' WHERE id = 445221;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        $tablePrefix = DB::getTablePrefix();
        DB::unprepared("UPDATE {$tablePrefix}area SET cname = '揭东县' WHERE id = 445221;");
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
