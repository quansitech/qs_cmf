<?php
namespace Common\Model;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Qscmf\Lib\DBCont;
use Qscmf\Lib\Tp3Resque\Resque;
use Qscmf\Lib\Tp3Resque\Resque\Job\Status;

class QueueModel extends \Gy_Library\GyListModel{
    
    public function __construct($name = '', $tablePrefix = '', $connection = '') {
        parent::__construct($name, $tablePrefix, $connection);
    }
    
    public function freshStatus(){
        $map['status'] = array('neq', DBCont::JOB_STATUS_COMPLETE);
        $ents = $this->where($map)->select();
        foreach($ents as $ent){
            $status = new Status($ent['id']);
            $new_status = $status->get();
            $ent['status'] = $new_status;
            $this->save($ent);
        }
    }
    
    public function refreshStatusOne($job_id){
        $ent = $this->getOne($job_id);
        
        $status = new Status($ent['id']);
        $new_status = $status->get();
        $ent['status'] = $new_status;
        $this->save($ent);
    }
    
    
    public function rebuildJob(){
        $map['status'] = array('not in', array(DBCont::JOB_STATUS_WAITING, DBCont::JOB_STATUS_COMPLETE));
        $ents = $this->where($map)->select();
        foreach($ents as $ent){
            $job = $ent['job'];
            $args = json_decode($ent['args'], true);
            
            $job_id = Resque::enqueue('default', $job, $args, true);
            $this->where(array('id' => $ent['id']))->delete();
            
            
            $ent['id'] = $job_id;
            $ent['status'] = DBCont::JOB_STATUS_WAITING;
            $ent['create_date'] = time();
            
            $this->add($ent);
        }
    }
    
    public function rebuildJobOne($job_id){
        $ent = $this->getOne($job_id);
        
        if(!$ent){
            $this->error = '任务不存在';
            return false;
        }
        
        if($ent['status'] == DBCont::JOB_STATUS_WAITING || $ent['status'] == DBCont::JOB_STATUS_COMPLETE){
            $this->error = "任务状态为等待或者完成，不可重启";
            return false;
        }
        
        $job = $ent['job'];
        $args = json_decode($ent['args'], true);

        $new_job_id = Resque::enqueue('default', $job, $args, true);
        $this->where(array('id' => $ent['id']))->delete();


        $ent['id'] = $new_job_id;
        $ent['status'] = DBCont::JOB_STATUS_WAITING;
        $ent['create_date'] = time();

        $this->add($ent);
        return true;
    }
    
}
