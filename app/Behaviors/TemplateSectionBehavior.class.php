<?php
namespace Behaviors;
class TemplateSectionBehavior extends \Think\Behavior{
    protected $config = array();
	protected $section = array();

    public function __construct(){
        $this->config['tmpl_section'] = C('TMPL_SECTION')?C('TMPL_SECTION'):'/\@section\:(.+?)\{%(.+?)%\}/is';
        $this->config['tmpl_section_register'] = C('TMPL_SECTION_REGISTER')?C('TMPL_SECTION_REGISTER'):'/\{\%section:(.+?)\%\}/is';
    }

    //行为执行入口
    public function run(&$param){
    	// dump($param);
    	$param = $this->parseSection($param);
    	$param = $this->parseSectionRegister($param);
    	// dump($param);
    }

    protected function parseSectionRegister($content){
        // dump($this->section);
       	$find = preg_match_all($this->config['tmpl_section_register'],$content,$matches);
        // dump($matches);
       	if(!$find) return $content;
       	for ($i=0; $i < count($matches[0]); $i++) {
            if($count = count($this->section[$matches[1][$i]]))
                for ($j=0; $j < $count; $j++) { 
                    $content = str_replace($matches[0][$i],$this->section[$matches[1][$i]][$j],$content);
                }
            else
                $content = str_replace($matches[0][$i],'',$content);
        }
    	return $content;
    }

    // 解析模板中的布局标签
    protected function parseSection($content) {
        // 读取模板中的布局标签
        $find = preg_match_all($this->config['tmpl_section'],$content,$matches);
        // dump($matches);
        if(!$find) return $content;
        //替换Layout标签
        for ($i=0; $i < count($matches[0]); $i++) { 
        	$content = str_replace($matches[0][$i],'',$content);
            if(!is_array($this->section[$matches[1][$i]])) $this->section[$matches[1][$i]]=array();
        	array_push($this->section[$matches[1][$i]],$matches[2][$i]);
        }
        return $content;
    }
}