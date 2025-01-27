### 旧版后台构建组件转新版文档（AI学习版）

ListBuilder转 Table示例

待分配利润增删改查管理页面

旧版实例代码
```php
<?php
namespace Admin\Controller;

use Gy_Library\DBCont;
use Gy_Library\GyListController;

class ProfitUnassignController extends GyListController{

    public function index(){
        $model = D('ProfitUnassign');
        $count = $model->getListForCount([]);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }

        $data_list = $model->getListForPage([], $page->nowPage, $page->listRows, 'create_date desc');

        $builder = new \Qscmf\Builder\ListBuilder();
        $builder = $builder->setMetaTitle('待分配利润列表');
        $builder->addTopButton('modal', ['title' => '新增'],'','',$this->buildAddModal())
            ->setNIDByNode(MODULE_NAME, 'ProfitUnassign', 'index')
            ->addTableColumn("amount", "待分配利润")
            ->addTableColumn("trans_date", '日期', 'date')
            ->addTableColumn('right_button', '操作', 'btn')
            ->setTableDataList($data_list)
            ->setTableDataPage($page->show())
            ->addRightButton('delete')
            ->build();
    }

    protected function buildAddModal(){
        $modal = new \Qs\ModalButton\ModalButtonBuilder();
        return
            $modal
                ->bindFormBuilder($this->add($modal->getModalDom()))
                ->setTitle("新增待分配利润")
                ->setBackdrop(false)
                ->setKeyboard(false);
    }

    public function add($modal_id = null){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            $model = D('ProfitUnassign');
            $r = $model->createAdd($data);
            if($r === false){
                $this->error($model->getError());
            }
            else{
                sysLogs('新增待分配利润, id:' . $r);

                $this->success(l('add') . l('success'), 'javascript:location.reload();');
            }
        }
        else {
            $builder = new \Qscmf\Builder\FormBuilder();

            $data_list = array(
                "trans_date"=> date("Y-m-d")
            );

            if($data_list){
                $builder->setFormData($data_list);
            }


            $builder
                ->setNIDByNode(MODULE_NAME, CONTROLLER_NAME, 'index')
                ->setPostUrl(U('add'))
                ->addFormItem("amount", "num", "待分配利润")
                ->addFormItem("trans_date", "date", "日期")
                ->setShowBtn(false);

            return $builder;
        }
    }

    public function delete(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要删除的数据');
        }
        if(is_string($ids)){
            $ids = explode(',', $ids);
        }
        $r = D("ProfitUnassign")->where(['id' => ['in', $ids]])->delete();
        if($r === false){
            $this->error(D("ProfitUnassign")->getError());
        }
        else{
            sysLogs('待分配利润, id: ' . $ids . ' 删除');
            $this->success('删除成功', 'javascript:location.reload();');
        }
    }
}
```



新版Table实例代码
```php
<?php
namespace Admin\Controller;

use AntdAdmin\Component\Modal\Modal;
use AntdAdmin\Component\Table;
use AntdAdmin\Component\Table\Pagination;
use AntdAdmin\Component\Form;
use AntdAdmin\Component\ColumnType\RuleType\Required;
use Gy_Library\GyListController;

class ProfitUnassignController extends GyListController{

    public function index(){
        $model = D('ProfitUnassign');
        $count = $model->getListForCount([]);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }

        $data_list = $model->getListForPage([], $page->nowPage, $page->listRows, 'create_date desc');

        $table = new Table();
        $table->setMetaTitle('待分配利润列表')
            ->actions(function (Table\ActionsContainer $container){
                $container->button('新增')
                    ->setProps(['type' => 'primary'])
                    ->modal((new Modal())->setWidth('500px')->setUrl(U('add'))->setTitle('新增待分配利润'));
            })
            ->columns(function (Table\ColumnsContainer $container) {
                $container->text('amount', '待分配利润');
                $container->date('trans_date', '日期');
                
                $container->action('', '操作')->actions(function (Table\ColumnType\ActionsContainer $container){
                    $container->delete();
                });
            })
            ->setDataSource($data_list)
            ->setPagination(new Pagination($page->nowPage,$page->listRows,$count))
            ->setSearch(false)
            ->render();
    }


    public function add(){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            $model = D('ProfitUnassign');
            $r = $model->createAdd($data);
            if($r === false){
                $this->error($model->getError());
            }
            else{
                sysLogs('新增待分配利润, id:' . $r);

                $this->success(l('add') . l('success'));
            }
        }
        else {
            $form = new \AntdAdmin\Component\Form();
            $form->setSubmitRequest('post', U('add'))
                ->setInitialValues(['trans_date' => date("Y-m-d")])
                ->columns(function (Form\ColumnsContainer $columns){
                    $columns->money('amount', '待分配利润')
                        ->addRule(new Required())
                        ->setFormItemWidth(24);
                    $columns->date('trans_date', '日期')
                        ->addRule(new Required())
                        ->setFormItemWidth(24);
                
                })
                ->actions(function(Form\ActionsContainer $actions){
                    $actions->button('提交')->submit();
                    $actions->button('重置')->reset();
                });

            return $form->render();
        }
    }

    public function delete(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要删除的数据');
        }
        if(is_string($ids)){
            $ids = explode(',', $ids);
        }
        $r = D("ProfitUnassign")->where(['id' => ['in', $ids]])->delete();
        if($r === false){
            $this->error(D("ProfitUnassign")->getError());
        }
        else{
            sysLogs('待分配利润, id: ' . $ids . ' 删除');
            $this->success('删除成功');
        }
    }
}
```