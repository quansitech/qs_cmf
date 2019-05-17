<?php
/**
 * Created by PhpStorm.
 * User: 95869
 * Date: 2019/3/12
 * Time: 9:51
 */

namespace Admin\Controller;


use Common\Builder\FormBuilder;
use Common\Builder\ListBuilder;
use Gy_Library\GyListController;

class JsErrLogController extends GyListController
{
    private function _filter(&$map=[]){
        $get_data=I('get.');
        isset($get_data['url']) && $map['url']=['like','%'.$get_data['url'].'%'];

        if (isset($get_data['create_date']) && $get_data['create_date']){
            $date=explode('-',$get_data['create_date']);
            $map['create_date']=[
                ['lt',strtotime($date[1])],
                ['gt',strtotime($date[0])]
            ];
        }
    }

    public function index(){
        $this->_filter($map);
        $model=D('JsErrlog');

        $count = $model->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }

        $data_list = $model->getListForPage($map, $page->nowPage, $page->listRows, 'create_date desc');

        $builder=new ListBuilder();
        $builder->setMetaTitle('前台js异常')
            ->setNID(990)
            ->setTableDataList($data_list)
            ->setTableDataPage($page->show())
            ->addSearchItem('create_date','date_range','出现时间')
            ->addSearchItem('url','text','url')
            ->addTableColumn('url','url')
            ->addTableColumn('msg','异常信息')
            ->addTableColumn('file','异常文件')
            ->addTableColumn('browser','浏览器')
            ->addTableColumn('create_date','出现时间','time')
            ->addTableColumn('right_button', '操作', 'btn')
            ->addRightButton('edit',['title'=>'查看'])
            ->display();
    }

    public function edit($id){
        $data=D('JsErrlog')->getOne($id);
        $builder=new FormBuilder();
        $builder->setMetaTitle('前台js异常详情')
            ->setNID(990)
            ->addFormItem('url','text','url','',[],[],'disabled')
            ->addFormItem('browser','text','浏览器','',[],[],'disabled')
            ->addFormItem('msg','text','异常信息','',[],[],'disabled')
            ->addFormItem('file','text','异常文件','',[],[],'disabled')
            ->addFormItem('line_no','text','行号','',[],[],'disabled')
            ->addFormItem('col_no','text','列号','',[],[],'disabled')
            ->addFormItem('stack','textarea','堆栈','',[],[],'disabled')
            ->addFormItem('user_agent','textarea','UA','',[],[],'disabled')
            ->addFormItem('create_date','datetime','出现时间','',[],[],'disabled')
            ->setFormData($data)
            ->setExtraHtml($this->fetch('edit_extra'))
            ->display();
    }
}