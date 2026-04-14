<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;
use Qscmf\Lib\Tp3Resque\Resque;
use Qscmf\Lib\Tp3Resque\Resque\Job\Status;

class Queue extends Model
{
    protected $table = 'queue';
    public $timestamps = false;
    protected $guarded = [];

    public static function getOne($id)
    {
        $ent = self::find($id);
        return $ent ? $ent->toArray() : null;
    }

    public static function refreshStatusOne($job_id)
    {
        $ent = self::getOne($job_id);
        if (!$ent) return false;

        $status = new Status($ent['id']);
        $new_status = $status->get();
        return self::where('id', $job_id)->update(['status' => $new_status]);
    }

    public static function rebuildJobOne($job_id)
    {
        $ent = self::getOne($job_id);

        if (!$ent) {
            return ['result' => false, 'error' => '任务不存在'];
        }

        if ($ent['status'] == DBCont::JOB_STATUS_WAITING || $ent['status'] == DBCont::JOB_STATUS_COMPLETE) {
            return ['result' => false, 'error' => '任务状态为等待或者完成，不可重启'];
        }

        $job = $ent['job'];
        $args = json_decode($ent['args'], true);
        $queue = $ent['queue'] ?: 'default';

        $new_job_id = Resque::enqueue($queue, $job, $args, true);
        self::where('id', $ent['id'])->delete();

        $ent['id'] = $new_job_id;
        $ent['status'] = DBCont::JOB_STATUS_WAITING;
        $ent['create_date'] = time();

        self::insert($ent);
        return ['result' => true];
    }
}
