<?php

namespace Gy_Library;

interface ICUDModel{
    
    function add($data = '', $options = array(), $replace = false);
    
    function edit($data = '', $options = array(), $msg = '');
    
    function del($id = '');
}

