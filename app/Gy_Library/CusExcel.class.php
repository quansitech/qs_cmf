<?php
namespace Gy_Library;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CusExcel
 *
 * @author 英乐
 */
class CusExcel{
    //put your code here
    private $_php_excel;
    
    public function __construct($excel_file = '') {
        import('Common.Util.PHPExcel');
        
        if(file_exists($excel_file)){
            $this->_php_excel = $this->load($excel_file);
        }
        else{
            $this->_php_excel = new \PHPExcel();
        }

    }
    
    protected function load($file){
        if(strpos($file, '.xlsx') === false && strpos($file, '.xls') === false){
            E('不是excel文件');
        }

        $php_excel = \PHPExcel_IOFactory::load($file);
        return $php_excel;
    }
    
    
    public function setActiveSheetIndexByName($name){
        $this->_php_excel->setActiveSheetIndexByName($name);
        return $this;
    }
    
    public function setCellValueByColumnAndRow($column, $row, $value){
        $letter = \PHPExcel_Cell::stringFromColumnIndex($column);
        if(strpos($value, 'picture:') !== false && strpos($value, 'picture:') !== null){
                
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $pic_path = substr($value, strlen('picture:'));
            if(!file_exists($pic_path)){
                return $this;
            }
            $objDrawing->setPath($pic_path);
            $objDrawing->setWidth(100);
            $objDrawing->setHeight(100);
            $objDrawing->setCoordinates("{$letter}{$row}");
        }
        // example list:item A@item A,item B,item C
        else if(strpos($value, 'list:') !== false && strpos($value, 'list:') !== null){
            $tmp_arr = explode('@', $value);
            $list_str = $tmp_arr[1];
            $data_value = substr($tmp_arr[0], strlen('list:'));
            $objValidation = $this->_php_excel->getActiveSheet()->getCellByColumnAndRow($column, $row)->getDataValidation();
            $objValidation->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation->setShowInputMessage(true);
            $objValidation->setShowErrorMessage(true);
            $objValidation->setShowDropDown(true);
            $objValidation->setErrorTitle('输入错误');
            $objValidation->setError('请选择列表中的值');
            if(strpos($list_str, ',') === false){
                $objValidation->setFormula1($list_str);
            }
            else{
                $objValidation->setFormula1('"' . $list_str . '"');
            }
            
            $this->_php_excel->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $row, $data_value);
        }
        else{
            $this->_php_excel->getActiveSheet()->setCellValueExplicitByColumnAndRow($column, $row, $value);
        }
        return $this;
    }
    
    public function download($file_name){
        $php_writer = \PHPExcel_IOFactory::createWriter($this->_php_excel, 'Excel5');

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'. $file_name .'"');
        header("Content-Disposition:attachment;filename={$file_name}");//attachment新窗口打印inline本窗口打印

        $php_writer->save('php://output');

    }
    
    public function getRow($column_count, $row){
        $column_list = array();

        for($col = 0; $col < $column_count; $col++){
            $cell_value = $this->_php_excel->getActiveSheet()->getCellByColumnAndRow($col, $row)->getValue();
            $column_list[] = (string)$cell_value;
        }
       
        return $column_list;
    }
    
    //flag_column 指选择哪一列作为循环结束标记，当该列的行数据为空就结束循环
    public function getRows($flag_column = ''){
        $col = $flag_column == '' ? 0 : $flag_column;
        $column_count = $this->getColumnCount(1);
        $r = 1;
        $cell_value = $this->_php_excel->getActiveSheet()->getCellByColumnAndRow($col, $r)->getValue();
        
        $row_list = array();
        while($cell_value != ''){
            $row_list[] = $this->getRow($column_count, $r);
            $r++;
            $cell_value = $this->_php_excel->getActiveSheet()->getCellByColumnAndRow($col, $r)->getValue();
        }
        return $row_list;
    }
    
    public function getColumnCount($row){
        $col = 0;
        do{
            $cell_value = $this->_php_excel->getActiveSheet()->getCellByColumnAndRow($col, $row)->getValue();
            $col++;
        }
        while(!empty($cell_value));
        return $col-1;
    }
    
}
