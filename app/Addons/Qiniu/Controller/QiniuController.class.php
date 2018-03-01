<?php
namespace Addons\Qiniu\Controller;
use Home\Controller\AddonsController;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class QiniuController extends AddonsController{
    
    public function __construct() {
        parent::__construct();
        
        $this->_admin_function_list = array(
            'setFilePic'
        );
    }
    
    public function setFilePic(){
        $url = I('post.url');
        $title = I('post.title');
        $ref_id = I('post.ref_id');
        $size = I('post.size');
        
        $data['title'] = $title;
        $data['url'] = $url;
        $data['ref_id'] = $ref_id;
        $data['size'] = $size;
        $data['cate'] = 'video';
        $data['upload_date'] = time();
        
        $file_pic_model = D('FilePic');
        $id = $file_pic_model->createAdd($data);
        if($id === false){
            $this->error($file_pic_model->getError());
        }
        else{
            $return['status'] = 1;
            $return['file_id'] = $id;
            $this->ajaxReturn($return);
        }
    }
    
    public function notify(){
        $_body = file_get_contents('php://input');
        $body = json_decode($_body, true);
        
        $code = $body['code'];
        $ref_id = $body['id'];
        $item = array_pop($body['items']);
        $input_key = $body['inputKey'];
        $key = $item['key'];
        $file_pic_model = D('FilePic');
        if($code == 0){
            $ent = $file_pic_model->getByRef_id($ref_id);
            $ent['url'] = str_replace($input_key, $key, $ent['url']);
            $ent['ref_id'] = '';
            $r = $file_pic_model->save($ent);
            if($r === false){
                $resp = $file_pic_model->errorInfo();
                http_response_code(500);
                echo json_encode($resp);
                return;
            }
            else{
                $resp = array('ret' => 'success');
                echo json_encode($resp);
            }
        }
    }
}

