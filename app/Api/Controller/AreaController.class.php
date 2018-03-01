<?php
namespace Api\Controller;
use Think\Controller;

class AreaController extends Controller{
    
    public function getArea(){
        $area = M('Area');
        $area_ents = $area->select();
        echo json_encode($area_ents);
    }
}
