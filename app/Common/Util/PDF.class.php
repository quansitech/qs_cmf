<?php
namespace Common\Util;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class PDF{
    
    public function __construct() {
        require_once(dirname(__FILE__).'/Tcpdf/tcpdf.php');
    }
    
    public function newInstance($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false){
        return new \TCPDF($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
    }
}
