<?php

namespace Admin\Controller;

use AntdAdmin\Component\ColumnType\RuleType\Min;
use AntdAdmin\Component\ColumnType\RuleType\Pattern;
use AntdAdmin\Component\ColumnType\RuleType\Required;
use AntdAdmin\Component\Form;
use AntdAdmin\Component\Modal\ModalContent;
use AntdAdmin\Component\Table;
use AntdAdmin\Component\Tabs;
use AntdAdmin\Controller\HasLayoutProps;
use AntdAdmin\Lib\Adapter\QsPage2Pagination;
use Gy_Library\DBCont;
use Gy_Library\GyListController;
use Think\Exception;

class TestController extends GyListController
{
    use HasLayoutProps;

    protected function _initialize()
    {
        parent::_initialize();
        $this->handleLayoutProps();
    }

    public function tabs()
    {
        $tabs = new Tabs();

        $table = new Table();
        $tabs->addTab('table', '列表页', $table);

        $form = new Form();
        $form->actions(function (Form\ActionsContainer $container) {
            $container->button('提交')->submit('post', U(''));
        });
        $tabs->addTab('form', '表单页', $form);
        $tabs->render();
    }

    /**
     * @throws \ReflectionException
     * @throws Exception
     */
    public function index()
    {
//        $map = [];
//        $user_model = D('Node');
//        $count = $user_model->getListForCount($map);
//        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
//        if($per_page === false){
//            $page = new \Gy_Library\GyPage($count);
//        }
//        else{
//            $page = new \Gy_Library\GyPage($count, $per_page);
//        }
//        $data_list = $user_model->getListForPage($map, $page->nowPage, $page->listRows);
//
//        $builder = new ListBuilderByInertia();
//        $builder->addTableColumn('id', 'ID');
//        $builder->addTableColumn('name', '节点名');
//        $builder->setTableDataList($data_list);
//        $builder->setTableDataPage($page->show());
//
//        $builder->build();

        $map = [];
        $model = D('Node');
        $count = $model->getListForCount($map);
        $page = new \Gy_Library\GyPage($count, 20);
        $data_list = D('Node')->getListForPage($map, $page->nowPage, $page->listRows);


        $table = new Table();
        $table->setMetaTitle('列表页');
        $table->columns(function (Table\ColumnsContainer $container) {
            $container->text('id', 'ID')->setSearch(false)->setWidth('100px')->setFixed('left');
            $container->text('name', '节点名')->editable();

            $container->select('status', '状态')->setOptions([
                DBCont::NORMAL_STATUS => [
                    'text' => '启用',
                    'status' => 'Success'
                ],
                DBCont::FORBIDDEN_STATUS => [
                    'text' => '禁用',
                    'status' => 'Warning'
                ],
            ])->editable();
            $container->dateTime('created_at', '创建时间')->hideInTable();

            $container->option('', '操作')->options(function (Table\ColumnType\OptionsContainer $container) {
                $container->link('查看')->setHref(U('edit', ['id' => '__id__']));

                $modal = new ModalContent();
                $modal->setType('form')
                    ->setUrl(U('form', ['id' => '__id__']));
                $container->link('弹窗')->modal('弹窗', $modal);
                $container->link('哈哈')->request('post', U('save'), ['name' => '__name__'])->addShowRules('id', [
                    new Min('number', 5)
                ]);
            });
        })
            ->actions(function (Table\ActionsContainer $container) {
                $container->button('新建')->setProps([
                    'type' => 'primary'
                ])->link(U('foo'));

                $modal = new ModalContent();
                $modal->setType('table')
                    ->setUrl(U('index'));
//                    ->setProps(new Table());
                $container->button('modal')->modal('modal', $modal);
                $container->button('tab页')->link(U('tabs'));
                $container->button('表单页')->link(U('form'));

                $container->button('重新加载');
                $container->button('关联选择')->relateSelection()->request('post', U('save'), [
                    'aaa' => '111',
                ]);

                $container->startEditable('开始编辑')->saveRequest('post', U('save'));
            })
            ->setRowSelection(true)
            ->setDataSource($data_list)
            ->setPagination(new QsPage2Pagination($page))
            ->setDefaultSearchValue(['name' => '1', 'status' => 1])
            ->render();
    }

    public function form()
    {
        if (IS_POST) {
//            $this->success('', U(''));
            return;
        }

        $this->setActiveNid(getNid(MODULE_NAME, CONTROLLER_NAME, 'foo'));

        $form = new Form();
        $form->setMetaTitle('表单页')
            ->columns(function (Form\ColumnsContainer $container) {
                $container->select('test', 'test')->setOptions([
                    'a' => '1',
                    'b' => '2'
                ])->setSearchUrl(U('search'));

                $container->area('area', '城市');

                $container->cascader('cascader', '级联')
                    ->setLoadDataUrl(U('loadData'));

                $container->ueditor('ueditor', '富文本')
                    ->setFormItemWidth(24)
                    ->setWidth('100%');
                $container->text('text', 'text')
                    ->setTips('以t开头')
                    ->setFormItemWidth(6)
                    ->addRule(new Required())
                    ->addRule(new Pattern('^t\w+$'));
                $container->text('text1', 'text1')->setFormItemWidth(18);
                $container->text('text2', 'text2')->addRule(new Required());
                $container->image('image', '图片')->setUploadRequest(U('/api/upload/upload', [
                    'cate' => 'image',
                ]))->setMaxCount(5)->setCrop('600/300');
                $container->file('file', '文件')->setUploadRequest(U('/api/upload/upload', [
                    'cate' => 'file',
                ]))->setMaxCount(5);

            })
            ->actions(function (Form\ActionsContainer $container) {
                $container->button('提交')->setProps([
                    'type' => 'primary'
                ])->submit('post', U(''));
                $container->button('重置')->reset();
                $modal = new ModalContent();
                $modal->setType('form')
                    ->setUrl(U('form'));
                $container->button('弹窗')->modal('弹窗', $modal);
            })
            ->setInitialValues([
                'text' => 111,
                'image' => '5,6',
                'file' => '8,9,10,11,12',
                'area' => 440111,
            ])

//            ->setReadonly(true)
            ->render();
    }

    public function save()
    {
        E('asfd4sf6da4s68adf');
        $this->error('保存失败', U('index'));
    }

    public function check()
    {

    }

    public function foo()
    {
        $table = new Table();
        $table->render();
    }

    public function test()
    {
        $this->inertia('Test/Test', [
            'index_url' => U('index'),
        ]);
    }

    public function search()
    {
        $keyWords = I('keyWords');
        if (!$keyWords) {
            return [];
        }
        $this->ajaxReturn(collect([
            ['label' => $keyWords, 'value' => $keyWords . '_value']
        ])->multiply(5));
    }

    public function loadData()
    {
        $this->ajaxReturn([
            [
                'label' => 'a',
                'value' => '1',
                'isLeaf' => false,
            ],
            ['label' => 'b', 'value' => '2']
        ]);
    }
}