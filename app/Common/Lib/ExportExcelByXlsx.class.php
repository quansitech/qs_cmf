<?php
namespace Common\Lib;

trait ExportExcelByXlsx{

    protected function exportExcelByXlsx(\Closure $genExcelList){
        $page = I('get.page');
        $rownum = I('get.rownum');

        $list = call_user_func($genExcelList, $page, $rownum);
        $this->ajaxReturn($list);
    }
}