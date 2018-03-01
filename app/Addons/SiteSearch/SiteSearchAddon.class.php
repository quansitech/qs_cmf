<?php
namespace Addons\SiteSearch;
use Addons\Addon;

class SiteSearchAddon extends Addon{
    public $info = array(
        'name' => 'SiteSearch',
        'title' => '全站搜索',
        'description' => '全站搜索功能',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.1'
    );
    
    public function install(){
        $this->createHook('siteSearch', '全站搜索');
        return true;
    }
    
    public function uninstall(){
        $this->delHook('siteSearch');
        return true;
    }
    
    public function siteSearch($query){
        
        $config = $this->getConfig();

        $model_attr = parse_config_attr($config['model']);
        $model_name_attr = parse_config_attr($config['model_name']);
        $url_attr = parse_config_attr($config['url']);
        $search_fields_attr = parse_config_attr($config['search_fields']);
        $field_map_attr = parse_config_attr($config['field_map']);

        $parm = array();
        for($i=0;$i<count($model_attr);$i++){
            $tmp['model'] = $model_attr[$i];
            $tmp['model_name'] = $model_name_attr[$i];
            $tmp['url'] = $url_attr[$i];
            $tmp['search_fields'] = $search_fields_attr[$i];
            $tmp['field_map'] = $field_map_attr[$i];
            
            $parm[] = $tmp;
        }
        
        $data_list = array();
        foreach($parm as $p){
            $map = array();
            $map[$p['search_fields']] = array('like', '%' . $query . '%');
            $ents = D($p['model'])->where($map)->select();
            foreach($ents as $ent){
                $ent['url'] = $this->_parseUrl($p['url'], $ent);
                $field_map_arr = explode('|', $p['field_map']);
                $ent['model_name'] = $p['model_name'];
                foreach($field_map_arr as $m){
                    list($key, $value) = explode('=', $m);
                    if(strlen($ent[$value])>0){
                        $ent[$key] = $ent[$value];
                    }
                }
                $data_list[] = $ent;
            }
        }
        
        usort($data_list, function($a, $b){
            if($a['date'] == $b['date']) 
                return 0;
            else
                return $a['date'] > $b['date'] ? -1 : 1;
        });
        $count = count($data_list);
        $per_page = C('HOME_PER_PAGE_NUM', null, false);
        //构建分页链接
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        $data_list = array_slice($data_list, ($page->nowPage - 1) * $page->listRows, $page->listRows);
        $this->assign('query', $query);
        $this->assign('pagination', $page->show());
        $this->assign('data_list', $data_list);
        $this->display(T('Addons://SiteSearch@default/search_result'));
    }
    
    private function _parseUrl($url, $ent){
        while(preg_match('/__(.+?)__/', $url, $matches)){
            $url = str_replace($matches[0], $ent[$matches[1]], $url);
        }
        return $url;
    }
}
