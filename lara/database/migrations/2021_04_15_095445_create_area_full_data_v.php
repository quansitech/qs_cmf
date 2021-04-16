<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreaFullDataV extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = <<<sql
CREATE VIEW qs_area_full_data_v AS SELECT *,id as country_id,0 as p_id,0 as c_id,0 as d_id,cname as full_cname1 FROM qs_area WHERE level = 0
UNION
SELECT *,upid as country_id,id as p_id,0 as c_id,0 as d_id,cname as full_cname1 FROM qs_area WHERE level = 1
UNION
SELECT c.*,p.upid as country_id,c.upid as p_id,c.id as c_id,0 as d_id,concat(p.cname,' ',c.cname) as full_cname1 FROM qs_area c, (SELECT * FROM qs_area WHERE level =1) p WHERE c.level = 2 and c.upid=p.id
UNION
SELECT d.*,p.upid as country_id,c.upid as p_id,d.upid as c_id,d.id as d_id,concat(p.cname,' ',c.cname,' ',d.cname) as full_cname1 FROM qs_area d, (SELECT * FROM qs_area WHERE level =1) p,(SELECT * FROM qs_area WHERE level =2) c WHERE d.level = 3 and d.upid=c.id and p.id=c.upid
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
        DB::unprepared("DROP VIEW qs_area_full_data_v;");
    }

}
