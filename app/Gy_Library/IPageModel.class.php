<?php

namespace Gy_Library;

interface IPageModel{
    
    function getListForCount($map);
    
    function getListForPage($map, $page, $list_rows);
}

