<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreaV extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tablePrefix = DB::getTablePrefix();
        $sql = <<<sql
CREATE VIEW {$tablePrefix}area_v AS SELECT *,id as country_id,0 as p_id,0 as c_id,0 as d_id,cname as full_cname1 FROM {$tablePrefix}area WHERE level = 0
UNION
SELECT *,upid as country_id,id as p_id,0 as c_id,0 as d_id,cname as full_cname1 FROM {$tablePrefix}area WHERE level = 1
UNION
SELECT c.*,p.upid as country_id,c.upid as p_id,c.id as c_id,0 as d_id,concat(p.cname,' ',c.cname) as full_cname1 FROM {$tablePrefix}area c, (SELECT * FROM {$tablePrefix}area WHERE level =1) p WHERE c.level = 2 and c.upid=p.id
UNION
SELECT d.*,p.upid as country_id,c.upid as p_id,d.upid as c_id,d.id as d_id,concat(p.cname,' ',c.cname,' ',d.cname) as full_cname1 FROM {$tablePrefix}area d, (SELECT * FROM {$tablePrefix}area WHERE level =1) p,(SELECT * FROM {$tablePrefix}area WHERE level =2) c WHERE d.level = 3 and d.upid=c.id and p.id=c.upid
sql;

        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tablePrefix = DB::getTablePrefix();
        DB::unprepared("DROP VIEW IF EXISTS {$tablePrefix}area_v;");
    }

}
