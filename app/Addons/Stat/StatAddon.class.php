<?php
namespace Addons\Stat;
use Addons\Addon;

class StatAddon extends Addon{
    public $info = array(
        'name' => 'Stat',
        'title' => '网站统计',
        'description' => '网站统计指标插件',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.1'
    );
    
    public function install(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}stat;");
        $model->execute("CREATE TABLE {$prefix}stat (`id` int(11) NOT NULL AUTO_INCREMENT primary key,`type` varchar(100) NOT NULL,`num` float NOT NULL,`ref_id` varchar(100) NOT NULL,`ref_key` varchar(100) NOT NULL, update_date int not null);");
        $this->createHook('stat', '数据统计');
        return true;
    }
    
    public function uninstall(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}stat;");
        $this->delHook('stat');
        return true;
    }
    
    public function stat($param){
        $type = $param[0];
        $num = $param[1];
        $ref_id = $param[2];
        $ref_key = $param[3];

        $stat_model = D('Addons://Stat/Stat');
        
        $map['type'] = $type;
        $map['ref_id'] = $ref_id;
        $map['ref_key'] = $ref_key;
        $stat_ent = $stat_model->where($map)->find();
        
        if($stat_ent){
            $stat_model->where($map)->setField('num', $stat_ent['num']+$num);
            $stat_model->where($map)->setField('update_date', time());
        }
        else{
            $data['type'] = $type;
            $data['num'] = $num;
            $data['ref_id'] = $ref_id;
            $data['ref_key'] = $ref_key;
            $data['update_date'] = time();
            $stat_model->add($data);
        }
    }
}
