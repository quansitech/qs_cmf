<?php

namespace Api\Controller;

class UploadController extends \Think\Controller{
    
//    private function uploadBase64($cate){
//        $post_key = I('get.post_key');
//        $post_data = I('post.');
//        $base64_string = $post_data[$post_key];
//        $key = 'UPLOAD_TYPE_' . strtoupper($cate);
//        
//        if(C($key, null, '') == ''){
//            $ajax = array(
//                    'Status' => 0,
//                    'info' => '没有该类型的上传项',
//                    'Time_stamp' => time(),
//                    'Data' => null
//                );
//                $this->ajaxReturn($ajax);
//        }
//        
//        $savename = uniqid().'.png';//localResizeIMG压缩后的图片都是jpeg格式
//        $image = base64_to_img( $base64_string, $savename );
//        $this->ajaxReturn($savename);
//    }
    
    private function upload($cate, $owner = 0){
        //IMAGE_THUMB_SCALE     =   1 ; //等比例缩放类型
        //IMAGE_THUMB_FILLED    =   2 ; //缩放后填充类型
        //IMAGE_THUMB_CENTER    =   3 ; //居中裁剪类型
        //IMAGE_THUMB_NORTHWEST =   4 ; //左上角裁剪类型
        //IMAGE_THUMB_SOUTHEAST =   5 ; //右下角裁剪类型
        //IMAGE_THUMB_FIXED     =   6 ; //固定尺寸缩放类型
        $gets = I('get.');
        foreach($gets as $k => $v){
            $$k = $v;
        }
        $key = 'UPLOAD_TYPE_' . strtoupper($cate);
        
        if(C($key, null, '') == ''){
            $ajax = array(
                    'Status' => 0,
                    'info' => '没有该类型的上传项',
                    'Time_stamp' => time(),
                    'Data' => null
                );
                $this->ajaxReturn($ajax);
        }
        $upload = new \Gy_Library\CusUpload(C($key));
        $info = $upload->upload();
        if (!$info) {
            $this->error($upload->getError());
        } else {
            $upload_config = C($key);

            $file = array_pop($info);
            $file_pic = D('FilePic');
            $data['file'] = $file['savepath'] . $file['savename'];
            $data['title'] = $file['name'];
            $data['size'] = $file['size'];
            $data['cate'] = $cate;
            $data['security'] = isset($upload_config['security']) ? 1 : 0;
            $data['owner'] = isset($upload_config['security']) ? ($owner != 0 ? $owner : session(C('USER_AUTH_KEY'))) : 0;
            
            //文件获取失败
            if(!$data['file']){
                $this->error('文件获取失败,请重新上传');
            }
            
            $id = $file_pic->createAdd($data);
            if ($id === false) {
                $this->error($file_pic->getError());
            } else {
                $para = array($upload_config['rootPath'].$file['savepath'] ,  $file['savename']);
                \Think\Hook::listen('afterUpload', $para);
                $file_url = showFileUrl($id);
                $data['file_id'] = $id;
                $data['url'] = $file_url;
                $data['size'] = format_filesize($data['size']);
                $ajax = array(
                    'Status' => 1,
                    'info' => 'success',
                    'Time_stamp' => time(),
                    'Data' => $data
                );
                $this->ajaxReturn($ajax);
            }
        }
    }
    
    public function __call($method,$args) {
        
//        if(strpos($method, '_') !== false){
//            $method_arr = explode('_', $method);
//            $type = $method_arr[1];
//            if($type == 'base64' && strtolower(substr($method_arr[0],0,6))=='upload'){
//                $cate = strtolower(substr($method_arr[0], 6));
//                $this->uploadBase64($cate);
//                return;
//            }
//        }
        
        if(strtolower(substr($method,0,6))=='upload') {
            $cate = strtolower(substr($method, 6));
            $this->upload($cate);
            return;
        }
        
        parent::__call($method, $args);
    }
    
    public function load($file_id){

        $file_pic = M('FilePic');
        $file_pic_ent = $file_pic->where(array('id' => $file_id))->find();
        
        //检查访问权限
        if(session('file_auth_key') != $file_pic_ent['owner']){
            header('Content-Type:text/html; charset=utf-8');
            exit('非法访问');
        }
        
        $imginfo = getimagesize(SECURITY_UPLOAD_DIR. '/' . $file_pic_ent['file']);
        
        if(empty($imginfo)){
            forceDownload(SECURITY_UPLOAD_DIR. '/' . $file_pic_ent['file'], $file_pic_ent['title']);
        }
        else{
            showImageByHttp(SECURITY_UPLOAD_DIR. '/' . $file_pic_ent['file']);
            
        }
    }
    
    //下载非安全控制文件
    public function downloadFile($file_id){
        $file_pic = M('FilePic');
        $file_pic_ent = $file_pic->where(array('id' => $file_id))->find();
        if($file_pic_ent['security'] == 1){
            E('非法访问');
        }
        //统计下载次数
        $param = array(\Addons\Stat\StatCont::FILE_DOWNLOAD, 1, $file_id, 'FilePic');
        \Think\Hook::listen('stat', $param);

        forceDownload(UPLOAD_DIR. '/' . $file_pic_ent['file'], $file_pic_ent['title']);
    }
   
}

