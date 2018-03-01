<?php
namespace Gy_Library;
use Think\Page;

class GyPage extends Page {

    public $nowPage;
    protected $_p = 'p';

    public function __construct($totalRows, $listRows=20, $parameter = array()){
        parent::__construct($totalRows,$listRows,$parameter);
        C('VAR_PAGE') && $this->_p = C('VAR_PAGE'); //设置分页参数名称
        $this->nowPage    = empty(I('get.' .$this->_p)) ? 1 : intval(I('get.' .$this->_p));
        $this->parameter[$this->_p] = '__PAGE__';
    }

    protected function _url($page,$hash='__PAGE__'){
        return str_replace(urlencode($hash), $page, urldecode($this->url));
    }

    public function unsetParameter($key){
        unset($this->parameter[$key]);
    }

	/**
     * 分页显示输出
     * @access public
     */
    public function show($rollPage=10) {
        //分页数据
        $tmp_array = array();
        if($this->totalRows <= 0){
            $tmp_array['show'] = 0;
            return '';
        }

        $tmp_array['totalRows'] = $this->totalRows;

        $this->rollPage = $rollPage;

        /* 生成URL */
        if($this->parameter){
            $param_str = '?';
            foreach($this->parameter as $k=>$v){
                $param_str .= $k . '=' . $v . '&';
            }
            $param_str = trim($param_str, '&');
        }
        $this->url = U(ACTION_NAME) . $param_str;

        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数
        if($this->totalPages <= 1){
            $tmp_array['show'] = 0;
        }
        else{
            $tmp_array['show'] = 1;
        }
        if(!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->nowPage = $this->nowPage!=null?$this->nowPage:1;

        /* 计算分页零时变量 */
        $now_cool_page      = $this->rollPage/2;
        //$now_cool_page_ceil = ceil($now_cool_page);
        $this->lastSuffix && $this->config['last'] = $this->totalPages;



        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
		$tmp_array['prev'] = array(
        	'url'=>$upRow>0?$this->_url($upRow):'javascript:;',
        	'title'=>$this->config['prev'],
            'class'=>$upRow>0?'':'disabled'
        );
        $tmp_array['next'] = array(
        	'url'=>$downRow <= $this->totalPages?$this->_url($downRow):'javascript:;',
        	'title'=>$this->config['next'],
            'class'=>$downRow <= $this->totalPages?'':'disabled'
        );
        $tmp_array['first'] = array(
        	'url'=>$this->nowPage != 1?$this->_url(1):'javascript:;',
        	'title'=>$this->config['first'],
            'class'=>$this->nowPage != 1?'':'disabled'
        );

        $tmp_array['last'] = array(
        	'url'=>$this->nowPage < $this->totalPages?$this->_url($this->totalPages):'javascript:;',
        	'title'=>$this->config['last'],
            'class'=>$this->nowPage < $this->totalPages?'':'disabled'
        );
        // 1 2 3 4 5
//        $tmp_array['linkPage']=array();
//        for($i=1;$i<=$this->rollPage;$i++){
//            if(($this->nowPage - $now_cool_page) <= 0 ){
//                $page = $i;
//            }elseif(($this->nowPage + $now_cool_page - 1) >= $this->totalPages){
//                $page = $this->totalPages - $this->rollPage + $i;
//            }else{
//                $page = $this->nowPage - $now_cool_page_ceil + $i;
//            }
//            if($page > 0 && $page != $this->nowPage){
//                if($page<=$this->totalPages){
//                    $item = array(
//		            	'url'=>$this->_url($page),
//		            	'title'=>$page
//		            );
//                }else{
//                    break;
//                }
//            }else{
//                // if($page > 0 && $this->totalPages != 1){
//
//                // }
//                $item = array(
//                    'url'=>'',
//                    'title'=>$page,
//                    'current'=>true
//                );
//            }
//            array_push($tmp_array['linkPage'], $item);
//        }

        $cool_page = ceil($now_cool_page);
        $tmp_array['linkPage'] = array();
        if($this->totalPages>$this->rollPage){
                if($this->nowPage>$cool_page){
                        $start_page = ($this->nowPage+$this->rollPage-$cool_page)>$this->totalPages ? $this->totalPages-$this->rollPage + 1 : $this->nowPage-$cool_page+1;
                }
                else{
                        $start_page = 1;
                }
        }
        else{
                $start_page = 1;
        }
        $total = $this->rollPage>$this->totalPages? $this->totalPages : $this->rollPage;
        for($i=0;$i<$total;$i++){
	$for_page = $start_page + $i;
	if($for_page != $this->nowPage){
		$item = array(
			'url'=>$this->_url($for_page),
			'title'=>$for_page
		);
	}
	else{
		$item = array(
			'url'=>'',
			'title'=>$for_page,
			'current'=>true
		);
	}
                array_push($tmp_array['linkPage'], $item);
        }

        return $tmp_array;
    }
}
