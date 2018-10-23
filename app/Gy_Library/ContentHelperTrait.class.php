<?php
namespace Gy_Library;

use Think\Model;

Trait ContentHelperTrait {

        public function formItemFilter($option){
            if(!($this->model instanceof Model)){
                E('model属性必须是Think\Model类型');
            }
            if(!($this->cate_model instanceof Model)){
                E('cate_model属性必须是Think\Model类型');
            }
            $resolveCateTree = $this->_cateTree();
            return function($form_data, $form_item) use ($option, $resolveCateTree){
                $tree = call_user_func($resolveCateTree, $form_data['cate_id']);
                $item_option = [];
                array_walk($form_item, function($item) use (&$item_option){
                    $item_option[$item['name']] = 'show';
                });

                foreach($tree as $v) {
                    array_walk($option[$v]['show'], function ($name) use (&$item_option) {
                        $item_option[$name] = 'show';
                    });

                    array_walk($option[$v]['hidden'], function ($name) use (&$item_option) {
                        $item_option[$name] = 'hidden';
                    });
                }

                $new_form_item = [];
                array_walk($form_item, function($item) use(&$item_option, &$new_form_item){
                    if($item_option[$item['name']] === 'show'){
                        $new_form_item[] = $item;
                    }
                });
                return $new_form_item;
            };
        }

    private function _cateTree(){
        $model = $this->cate_model;
        return function($cate_id) use ($model){
            $cate_ent = $model->getOne($cate_id);
            if(!$cate_ent){
                E('分类不存在');
            }

            $tree = [$cate_id];
            while($cate_ent && $cate_ent['pid'] != 0){
                array_push($tree, $cate_ent['pid']);
                $cate_ent = $model->getOne($cate_ent['pid']);
            }
            return array_reverse($tree);
        };
    }
}