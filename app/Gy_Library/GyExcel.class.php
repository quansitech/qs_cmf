<?php

namespace Gy_Library;

class GyExcel {
    
    private $_php_excel;
    
    private $_column_width;
    
    private $_row_height;
    
    private $_error;
    
    public function __construct($column_width = 20, $row_height = 100, $excel_file = '') {
        import('Common.Util.PHPExcel');
        
        if(file_exists($excel_file)){
            $this->_php_excel = $this->load($excel_file);
        }
        else{
            $this->_php_excel = new \PHPExcel();
        }
        
        $this->_column_width = $column_width;
        $this->_row_height = $row_height;
    }
    
    //将数据导出excel并下载
    public function exportToDownload($list, $file_name = 'volunteer.xls'){
        for($i = 1; $i<=count($list); $i++){
            $this->_fillRow($this->_php_excel, $i, $list[$i-1]);
        }
        
        $php_writer = \PHPExcel_IOFactory::createWriter($this->_php_excel, 'Excel5');
        //$file_name = mkTempFile('./export/excel', 'xlsx');
        try{
//            $php_writer->save($file_name);
//            
//            $f = fopen($file_name, "r");
//            Header ( "Content-type: application/octet-stream" );  
//            Header ( "Accept-Ranges: bytes" );  
//            Header ( "Accept-Length: " . filesize ( $file_name ) );  
//            Header ( "Content-Disposition: attachment; filename=" . $file_name);  
//
//            echo fread($f, filesize($file_name));  
//            fclose($f);  
//            exit();
            
            header('pragma:public');
            header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'. $file_name .'"');
            header("Content-Disposition:attachment;filename={$file_name}");//attachment新窗口打印inline本窗口打印
            
            $php_writer->save('php://output');
            return true;
        }
        catch(PHPExcel_Writer_Exception $ex){
            $this->_error = $ex->getMessage();
            return false;
        }
    }
    
    public function importFromExcel($file){
        $php_excel = $this->load($file);
        return $this->_loopRow($php_excel);
    }
    
    public function load($file){
        if(strpos($file, '.xlsx') === false && strpos($file, '.xls') === false){
            $this->_error = '不是excel文件';
            return false;
        }

        $php_excel = \PHPExcel_IOFactory::load($file);
        return $php_excel;
    }
    
    public function getError(){
        return $this->_error;
    }
    
    private function _loopRow(&$php_excel){
        
        $column_count = $this->getColumnCount($php_excel, 1);
        $r = 1;
        $cell_value = $php_excel->getActiveSheet()->getCellByColumnAndRow(0, $r)->getValue();
        
        $row_list = array();
        while($cell_value != ''){
            $row_list[] = $this->readRow($php_excel, $r, $column_count);
            $r++;
            $cell_value = $php_excel->getActiveSheet()->getCellByColumnAndRow(0, $r)->getValue();
        }
        return $row_list;
    }
    
    public function readRow(&$php_excel, $row, $column_count){
        $column_list = array();
        for($col = 0; $col < $column_count; $col++){
            $cell_value = $php_excel->getActiveSheet()->getCellByColumnAndRow($col, $row)->getValue();
            $column_list[] = (string)$cell_value;
        }
        return $column_list;
    }
    
    public function getColumnCount(&$php_excel, $row){
        $col = 0;
        do{
            $cell_value = $php_excel->getActiveSheet()->getCellByColumnAndRow($col, $row)->getValue();
            $col++;
        }
        while(!empty($cell_value));
        return $col-1;
    }
    
    private function _fillRow(&$php_excel, $row, $data_arr){
        $n = 0;
        if($row != 1){
            $php_excel->getActiveSheet()->getRowDimension($row)->setRowHeight($this->_row_height);
        }
        foreach($data_arr as $data){
            $letter = \PHPExcel_Cell::stringFromColumnIndex($n);
            $php_excel->getActiveSheet()->getColumnDimension($letter)->setWidth($this->_column_width);
            if(strpos($data, 'picture:') !== false && strpos($data, 'picture:') !== null){
                
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $pic_path = substr($data, strlen('picture:'));
                if(!file_exists($pic_path)){
                    $n++;
                    continue;
                }
                $objDrawing->setPath($pic_path);
                $objDrawing->setWidth(100);
                $objDrawing->setHeight(100);
                $objDrawing->setCoordinates("{$letter}{$row}");
                $objDrawing->setWorksheet($php_excel->getActiveSheet());
            }
            // example list:item A@item A,item B,item C
            else if(strpos($data, 'list:') !== false && strpos($data, 'list:') !== null){
                $tmp_arr = explode('@', $data);
                $list_str = $tmp_arr[1];
                $data_value = substr($tmp_arr[0], strlen('list:'));
                $objValidation = $php_excel->getActiveSheet()->getCellByColumnAndRow($n, $row)->getDataValidation();
                $objValidation->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST);
                $objValidation->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                $objValidation->setAllowBlank(false);
                $objValidation->setShowInputMessage(true);
                $objValidation->setShowErrorMessage(true);
                $objValidation->setShowDropDown(true);
                $objValidation->setErrorTitle('输入错误');
                $objValidation->setError('请选择列表中的值');
                $objValidation->setFormula1('"' . $list_str . '"');
                $php_excel->getActiveSheet()->setCellValueExplicitByColumnAndRow($n, $row, $data_value);
            }
            else{
                $php_excel->getActiveSheet()->setCellValueExplicitByColumnAndRow($n, $row, $data);
            }
            $n++;
        }
    }
    
    public function setActiveSheetIndexByName($name){
        $this->_php_excel->setActiveSheetIndexByName($name);
    }
    
    
}
