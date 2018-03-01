<?php
namespace Addons\Donate\Model;
use \Addons\Donate\DonateCont;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DonateHeaderModel
 *
 * @author tider
 */
class DonateHeaderModel extends \Gy_Library\GyListModel{
    protected $_validate = array(
        array('status', 'require', '必选填写用户状态'),
        array('status', array(DonateCont::DONATE_STATUS_NOPAY, DonateCont::DONATE_STATUS_PAID), '{%STATUS_OUT_OF_RANGE}', parent::MUST_VALIDATE, 'in', parent::MODEL_BOTH),
        array('total_amount', array(0,99999999), '捐赠金额超出合理范围', parent::VALUE_VALIDATE, 'between', parent::MODEL_BOTH),
        array('total_amount', 0, '捐赠金额不能为零', parent::VALUE_VALIDATE, 'notequal', parent::MODEL_BOTH),
        array('channel_id', 'require', '必须填写捐赠方式'),
        array('channel_id', 'checkDonateContSetting', '捐赠方式不合法', parent::MUST_VALIDATE, 'callback', parent::MODEL_BOTH, array('getPayChannelList')),
        array('mail_type', 'checkDonateContSetting', '反馈方式不合法', parent::MUST_VALIDATE, 'callback', parent::MODEL_BOTH, array('getDonateMailTypeList'))
    );
     
    protected $_auto = array(
        array('donate_date', "time", parent::MODEL_INSERT, 'function'),
    );
    
    protected $_delete_auto = array( 
        array('delete',  'Addons://Donate/DonateDetail', array('id' => 'header_id')),
    );
    
    public function checkDonateContSetting($value, $get_list_fun){
        $list = DonateCont::$get_list_fun();
        return isset($list[$value]);
    }
    

    public function donate($header_data, $donate_detail, $remark = ''){
        if(!is_array($donate_detail)){
            E('donate_detail 不是数组格式');
        }

        $this->startTrans();
        try{
            
            $header_id = $this->createAdd($header_data);
            
            if($header_id === false){
                E($this->getError());
            }
            
            $line_no = 1;
            $donate_detail_model = D('Addons://Donate/DonateDetail');
            $total_amount = 0;
            foreach($donate_detail as $value){
                $detail_data['header_id'] = $header_id;
                $detail_data['line_no'] = $line_no++;
//                $detail_data['amount'] = $value['sum'];
//                $detail_data['ref_id'] = $value['id'];
//                $detail_data['ref_key'] = $value['type'];
//                
//                $total_amount += $value['sum'];
                $detail_data['amount'] = $value['amount'];
                $detail_data['ref_id'] = $value['ref_id'];
                $detail_data['ref_key'] = $value['ref_key'];
                
                $total_amount += $value['amount'];
                
                $r = $donate_detail_model->createAdd($detail_data);
                if($r === false){
                    E($donate_detail_model->getError());
                }
            }
            
            $this->where(array('id' => $header_id))->setField('total_amount', $total_amount);
            
            //if($header_data['uid'] != 0 && $header_data['status'] == DBCont::DONATE_STATUS_PAID){
            //    $para = array($header_data['uid'], $total_amount, $header_id, 'donateHeader', $remark);
                //\Think\Hook::listen('donated', $para); 
            //}
            
            $this->commit();
            return $header_id;
        } catch (\Think\Exception $ex) {
            $this->rollback();
            $this->error = $ex->getMessage();
            return false;
        }
    }
    
    public function alipayDonate($donate_id, $buyer_info){
        
        $ent = $this->getOne($donate_id);
        
        //已经完成支付操作，不要重复提交
        if($ent['status'] == DonateCont::DONATE_STATUS_PAID){
            return true;
        }
        
        $ent['status'] = DonateCont::DONATE_STATUS_PAID;
        $ent['donate_date'] = time();
        $ent['buyer_info'] = json_encode($buyer_info);

        $this->startTrans();
        try{
            C('TOKEN_ON', false);
            $r = $this->createSave($ent);
            if($r === false){
                E($this->getError());
            }
            
//            if($ent['uid'] != 0){
//                $remark = '支付宝在线合并捐赠';
//                $para = array($ent['uid'], $ent['total_amount'], $ent['id'], 'donateHeader', $remark);
//                \Think\Hook::listen('donated', $para); 
//            }
            
            $this->commit();
            return true;
        } catch (\Think\Exception $ex) {
            $this->rollback();
            $this->error = $ex->getMessage();
            return false;
        }
    }
    
    public function donateList($donate_date_range = '', $channel_id = '', $donator = '', $ref_name = '', $page = '', $list_row = ''){
        return $this->_getDonateList('list', $donate_date_range, $channel_id, $donator, $ref_name, $page, $list_row);
    }
    
    public function donateListCount($donate_date_range = '', $channel_id = '', $donator = '', $ref_name = ''){
        return $this->_getDonateList('count', $donate_date_range, $channel_id, $donator, $ref_name);
    }
    
    private function _getDonateList($type, $donate_date_range = '', $channel_id = '', $donator = '', $ref_name = '', $page = '', $list_row = ''){
        $count_select = 'select count(*) count ';
        $select = 'select h.donate_date, h.channel_id, h.secret, h.name, d.amount, d.ref_id, d.ref_key,h.offline,h.id,h.remark ';
        $from_where = 'from __DONATE_HEADER__ h, __DONATE_DETAIL__ d where h.id=d.header_id and h.status=' . DonateCont::DONATE_STATUS_PAID;
        $order = ' order by h.donate_date desc,d.line_no asc';
        
        if($donate_date_range){
            $donate_date_range_arr = explode(' - ', $donate_date_range);
            $from_date_time = strtotime($donate_date_range_arr[0] . ' 00:00:00');
            $to_date_time = strtotime($donate_date_range_arr[1] . ' 23:59:59');
            $from_where .= " and h.donate_date between {$from_date_time} and {$to_date_time}";
        }
        
        if($channel_id){
            $from_where .= ' and h.channel_id=' . $channel_id;
        }
        
        if($donator){
            $from_where .= " and h.name like '%{$donator}%' and h.secret!=" . DonateCont::PUBLISH_FORBID;
        }
        
        if($ref_name){
            $ref_key_arr = D('Addons://Donate/DonateDetail')->distinct('ref_key')->getField('ref_key', true);
            
            foreach($ref_key_arr as $ref_key){
                $map['title'] = array('like', '%' . $ref_name . '%');
                $ids = D(ucfirst($ref_key))->where($map)->getField('id', true);
                $ids = implode(',', $ids);
                
                if($ids){
                    $or_where = empty($or_where) ? "(ref_id in ({$ids}) and ref_key='{$ref_key}')" : $or_where . " or (ref_id in ({$ids}) and ref_key='{$ref_key}')";
                }
            }
            
            $or_where = empty($or_where) ? '1!=1' : $or_where;
            $from_where .= " and ({$or_where})";
        }
        
        if($type == 'count'){
            $count = M()->query($count_select . $from_where);
            return $count[0]['count'];
        }
        
        
        if($page != '' && $list_row != ''){
            $limit = ' limit ' . ($page - 1) * $list_row . ',' . $list_row;
        }
        else{
            $limit='';
        }
        
        $donation_list = M()->query($select . $from_where . $order . $limit);
        return $donation_list;
        
    }
    
    //线下捐赠批量新
    //$header_datas 为$header_data的集合
    //$detail_datas 与 $header_datas 大小相等
    public function addBatchByExcel($header_datas, $detail_datas){
        if(count($header_datas) == 0 || count($detail_datas) == 0){
            $this->error = '参数数组不能为空';
            return false;
        }
        
        $this->startTrans();
        try{
            foreach($header_datas as $k => $v){
                $r = $this->add($v);
                if($r === false){
                    E($this->getError());
                }

                $detail_datas[$k]['header_id'] = $r;
                $detail_model = D('Addons://Donate/DonateDetail');
                if($detail_model->add($detail_datas[$k]) === false){
                    E($detail_model->getError());
                }
            }
            $this->commit();
            return true;
        }
        catch (\Think\Exception $ex) {
            $this->rollback();
            $this->error = $ex->getMessage();
            return false;
        }
    }
    
}
