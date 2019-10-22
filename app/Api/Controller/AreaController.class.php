<?php
namespace Api\Controller;
use Think\Controller;

class AreaController extends Controller{
    
    public function getArea(){
        $area = M('Area');
        $area_ents = $area->select();
        echo json_encode($area_ents);
    }

    public function getProvince(){
        $map['level'] = 1;

        $province_list = D('Area')->where($map)->field('id,cname')->select();
        $this->ajaxReturn($province_list);
    }

    public function getCityByProvince($province_id){
        $map['upid'] = $province_id;
        $map['level'] = 2;

        $city_list = D('Area')->where($map)->field('id,cname1')->select();
        $this->ajaxReturn($city_list);
    }

    public function getDistrictByCity($city_id){
        $map['upid'] = $city_id;
        $map['level'] = 3;

        $district_list = D('Area')->where($map)->field('id,cname')->select();
        $this->ajaxReturn($district_list);
    }

    public function getAreaEnt($id){
        $ent = D('Area')->find($id);
        $this->ajaxReturn($ent);
    }

}
