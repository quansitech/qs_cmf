<?php
namespace Api\Controller;
use Think\Controller;
use Illuminate\Database\Capsule\Manager as Capsule;

class AreaController extends Controller{

    public function getArea(){
        $area_ents = Capsule::table('area')->get();
        echo json_encode($area_ents);
    }

    public function getProvince(){
        $map['level'] = 1;

        $province_list = Capsule::table('area')->where($map)->select('id', 'cname')->get()->toArray();
        $this->ajaxReturn($province_list);
    }

    public function getCityByProvince($province_id){
        $map['upid'] = $province_id;
        $map['level'] = 2;

        $city_list = Capsule::table('area')->where($map)->select('id', 'cname1')->get()->toArray();
        $this->ajaxReturn($city_list);
    }

    public function getDistrictByCity($city_id){
        $map['upid'] = $city_id;
        $map['level'] = 3;

        $district_list = Capsule::table('area')->where($map)->select('id', 'cname')->get()->toArray();
        $this->ajaxReturn($district_list);
    }

}
