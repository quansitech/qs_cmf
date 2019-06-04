<?php



function getAreaStrByIds($ids, $name = 'cname1'){
    if($ids == ''){
        return '';
    }

    $area = M('Area');
    $id_arr = explode(',', $ids);
    $area_arr = array();
    foreach($id_arr as $id){
        $ent = $area->where(array('id' => $id))->find();
        $area_arr[] = $ent[$name];
    }
    if(count($area_arr) > 0){
        return join(',', $area_arr);
    }
    else{
        return '';
    }
}

function getAreaNameByID($id, $name = 'cname1'){
    $area = M('Area');
    $area_ent = $area->find($id);
    return $area_ent[$name];
}

function getFullAreaByID($id){
    $area = M('Area');

    $area_ent = $area->find($id);
    if($area_ent['level'] >1){
        $p_name = getFullAreaByID($area_ent['upid']);
        return $p_name . ' ' . $area_ent['cname'];
    }
    else{
        return $area_ent['cname'];
    }
}

//通过城市名称获取到对应城市ID，返回false代表获取失败
//$area_arr 为城市名称数组
function getIdByFullArea($area_arr){
    $area = M('Area');

    $pid = 0;
    foreach($area_arr as $v){
        $map['cname'] = array('like', '%' . $v . '%');
        if($pid !== 0){
            $map['upid'] = $pid;
        }
        $area_ent = $area->where($map)->select();
        if(count($area_ent) != 1){
            return false;
        }
        $pid = $area_ent[0]['id'];
    }
    return $pid == 0 ? false : $pid;
}


function mkTempFile($path, $ext = ''){
    if(!is_dir($path)){
        mkdir($path, 0777, true);
    }
    $file_name = md5(time());
    if($ext != ''){
        $file_name .= '.' . $ext;
    }
    return $path . '/' . $file_name;
}

function mkFile($file, $content){
    $dir = dirname($file);
    if(!is_dir($dir)){
        mkdir($dir, 0755, true);
    }

    if(!file_exists($file)){
        file_put_contents($file, $content);
    }
}

function sysLogs($message='未知') {
    $syslogs = M("Syslogs");
    $data = array();
    $ip = get_client_ip();
    $data['modulename'] = getmodulename();
    $data['actionname'] = getControllerName();
    $data['opname'] = getActionName();
    $data['message'] = $message;
    $data['userid'] = session('?' . C('USER_AUTH_KEY'))? session(C('USER_AUTH_KEY')) : '0';
    $data['userip'] = $ip;
    $data['create_time'] = time();
    $syslogs->add($data);
}

function getClassFromDir($path, $name_space, $parent_class_name = ''){
    $files = array();
    $class_list = array();
    searchDir($path, $files);

    foreach($files as $file){
        if(preg_match('/(\w+).class.php$/', basename($file), $matches)){
            $class_name = $matches[1];

            $class = new \ReflectionClass($name_space . $class_name);
            if($parent_class_name == ''){
                $class_list[] = $class->newInstance();
                continue;
            }
            else if($class->getParentClass() !== false && $class->getParentClass()->getShortName() == $parent_class_name){
                $class_list[] = $class->newInstance();
                continue;
            }
        }
    }
    return $class_list;
}

function getClassNameFromDir($path, $name_space, $parent_class_name = ''){
    $files = array();
    $class_name_list = array();
    searchDir($path, $files);

    foreach($files as $file){
        if(preg_match('/(\w+).class.php$/', basename($file), $matches)){
            $class_name = $matches[1];

            if($parent_class_name == ''){
                $class_name_list[] = $class_name;
                continue;
            }

            $class = new \ReflectionClass($name_space . $class_name);

            if($class->getParentClass() !== false && $class->getParentClass()->getShortName() == $parent_class_name){
                $class_name_list[] = $class_name;
                continue;
            }
        }
    }
    return $class_name_list;
}

//返回规则类名称
//function getRuleClass($rule){
//    return '\\Common\\Rule\\' . $rule;
//}

//返回钩子绑定的插件
function getHookAddons($hook_name){
        $addons_ents = D('Addons')->where(array('status'=> \Qscmf\Lib\DBCont::NORMAL_STATUS))->select();
        $return = array();
        foreach($addons_ents as $ent){
            $class_name = get_addon_class($ent['name']);
            $methods = get_class_methods($class_name);
            if(array_intersect($methods, array($hook_name))){
                $return[] = $ent['name'];
            }
        }
        return $return;
}

function getMemberNickName($member_id){
    $member_ent = D('TeamMember')->getOne($member_id);
    return $member_ent['nick_name'] != '' ? $member_ent['nick_name'] : $member_ent['name'];
}

function getUserName($uid){
    if($uid == 0){
        return '系统';
    }

    return D('User')->getUserName($uid);
}

function getUserRealName($uid){
    $user_ent = D('User')->getOne($uid);
    if($user_ent['user_type'] == 'person'){
        $profile_ent = D('PersonProfile')->getByUid($uid);
        $name = $profile_ent['real_name'];
    }
    else if($user_ent['user_type'] == 'company'){
        $company_ent = D('CompanyProfile')->getByUid($uid);
        $name = $company_ent['contact'];
    }
    return $name;
}

function getUserRealNameByMobile($mobile){
    $ent = D('User')->where(array('telephone' => $mobile, 'user_type' => 'person'))->find();
    if($ent){
        return getUserRealName($ent['id']);
    }
    else{
        return '';
    }
}


function getMenu($type){
    $menu_model = D('Menu');

    return $menu_model->getMenuList($type);
}

function getMenuTree($pid = 0, $type='', $strip = 0){
    $menu_model = D('Menu');

    $menu_ents = $menu_model->getMenuList($type, $pid);
    $menu_tree= array();
    foreach($menu_ents as $ent){
        if($ent['id'] == $strip){
            continue;
        }

        $menu_tree[] = $ent;
        $child_menu_ents = getMenuTree($ent['id'], $type);
        foreach($child_menu_ents as $child_ent){
            $menu_tree[] = $child_ent;
        }
    }
    return $menu_tree;
}


function get_random_str($length = 10) {
        $str = '';
        for($i = 0; $i < $length; $i ++) {
                switch (mt_rand ( 0, 2 )) {
                        case 0 :
                                //数字
                                $str .= mt_rand ( 0, 9 );
                                break;
                        case 1 :
                                //大写字母
                                $str .= chr ( mt_rand ( 65, 90 ) );
                                break;
                        case 2 :
                                //小写字母
                                $str .= chr ( mt_rand ( 97, 122 ) );
                                break;
                }
        }
        return $str;
}

function generateInitPwd($length = 8){
    $str = substr(md5(time()), 0, $length);
    return $str;
}


function maskName($name, $mask = '*'){
    $len = mb_strlen($name, 'utf-8');
    $first = mb_substr($name, 0, 1, 'utf-8');

    //名字为空，直接返回
    if($len == 0){
        return $name;
    }

    //只有一个字，直接返回屏蔽字符
    if($len == 1){
        return $mask;
    }

    //大于1个字的都显示第一个字，后面用屏蔽字符取代
    if($len > 1){
        return $first . str_repeat($mask, $len - 1);
    }
}

//可逆加密算法
function encrypt($data, $key)
{
	$key	=	md5($key);
    $x		=	0;
    $len	=	strlen($data);
    $l		=	strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l)
        {
        	$x = 0;
        }
        $char .= $key{$x};
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
    }
    return base64_encode($str);
}

//可逆解密算法
function decrypt($data, $key)
{
    $key = md5($key);
    $x = 0;
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l)
        {
        	$x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
        {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }
        else
        {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}



//开发工具类函数 begin

//author: tider
//version: 1.0
//刷新缓存内容，显示于浏览器
//$msg 要显示的网页内容
function flushWebContent($msg){
    echo $msg;
    ob_flush();
    flush();
}

//生成hash码
function hashKey($para){
    $str = '';
    foreach($para as $k => $v){
        $str .= $k . '=' . $v . '&';
    }
    $str = substr($str, 0, strlen($str)-1);
    return md5($str);
}

//$end_date 格式 Y-m-d
function genEndDateTime($end_date){
    if(!$end_date){
        return false;
    }

    return strtotime('-1 second', strtotime('+1 day', strtotime($end_date)));
}

//$year_month 格式 Y-m
function genEndMonthTime($year_month){
    if(!$year_month){
        return false;
    }

    return strtotime('-1 second', strtotime('+1 month', strtotime($year_month)));
}

//返回上个月最后一天的时间值
function lastMonth($time){
    $t = date('Y-m', $time);
    return strtotime('-1 day', strtotime($t));
}

//返回查询月第一天的时间值
function monthFirstDay($time){
    $str = date('Y-m', $time);
    return strtotime($str);
}

//将图片文件转换成Base64
function convertImgToBase64ByFile($file_path){
    $type = getimagesize($file_path);
    $fp = fopen($file_path, 'r');
    $file_content=chunk_split(base64_encode(fread($fp,filesize($file_path))));
    switch($type[2]){//判读图片类型
        case 1:$img_type="gif";break;
        case 2:$img_type="jpg";break;
        case 3:$img_type="png";break;
    }
    $img='data:image/'.$img_type.';base64,'.$file_content;//合成图片的base64编码
    fclose($fp);
    return $img;
}

function getImgByFilePath($file_path, $width = ''){
    $img_html = '<img src="' . convertImgToBase64ByFile($file_path) . '"';
    if(!empty($width)){
        $img_html .= ' width="' . $width . 'px"';
    }
    $img_html .= '/>';
    return  $img_html;
}

//通过图片id获取图片，并将其转换成base64
function convertImgToBase64ByFileId($file_id, $prefix = ''){
    $file_pic = M('FilePic');
    $file_pic_ent = $file_pic->where(array('id' => $file_id))->find();

    if($file_pic_ent['security'] == 0){
        $path = UPLOAD_DIR;
    }
    else{
        $path = SECURITY_UPLOAD_DIR;
    }

    $file_name = basename(UPLOAD_DIR . '/' . $file_pic_ent['file']);

    $file_path = $path . '/' . $file_pic_ent['file'];
    if($prefix != ''){
        $file_path = $path . '/' . str_replace($file_name, $prefix . '_' . $file_name, $file_pic_ent['file']);
    }
    return convertImgToBase64ByFile($file_path);
}


//文件下载
function forceDownload($file, $file_name = '') {
    $file_name = $file_name == '' ? basename($file) : $file_name;
    if ((isset($file))&&(file_exists($file))) {
       header("Content-type: application/force-download");
       header('Content-Disposition: inline; filename="' . $file . '"');
       header("Content-Transfer-Encoding: Binary");
       header("Content-length: ".filesize($file));
       header('Content-Type: application/octet-stream');
       header('Content-Disposition: attachment; filename="' . $file_name  . '"');
       readfile("$file");
    }
    else {
       echo "No file selected";
    }
}

//下载用户上传的文件
function downloadFile($file_id){
    $file_pic = M('FilePic');
    $file_pic_ent = $file_pic->where(array('id' => $file_id))->find();
    if($file_pic_ent){
        forceDownload(UPLOAD_DIR . '/' .$file_pic_ent['file']);
    }
}


/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

//将从list_to_tree转换成的tree转换成树状结构下来列表
function genSelectByTree($tree, $child='_child', $level = 0){
    $select = array();
    foreach ($tree as $key => $data){
        if(isset($data[$child])){
            $data['level'] = $level;
            $select[] = $data;
            $child_list = genSelectByTree($data[$child], $child, $level+1);
            foreach($child_list as $k=>$v){
                $select[] = $v;
            }
        }else{
            $data['level'] = $level;
            $select[] = $data;
        }
    }
    return $select;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 */
function tree_to_list($tree, $child = '_child', $order='id', &$list = array()){
    if(is_array($tree)) {
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if(isset($reffer[$child])){
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        $list = list_sort_by($list, $order, $sortby='asc');
    }
    return $list;
}

function get_addon_class($name){
    $class = "Addons\\{$name}\\{$name}Addon";
    return $class;
}

function int_to_string(&$data,$map=array('status'=>array(1=>'正常',-1=>'删除',0=>'禁用',2=>'未审核',3=>'草稿'))) {
    if($data === false || $data === null ){
        return $data;
    }
    $data = (array)$data;
    foreach ($data as $key => $row){
        foreach ($map as $col=>$pair){
            if(isset($row[$col]) && isset($pair[$row[$col]])){
                $data[$key][$col.'_text'] = $pair[$row[$col]];
            }
        }
    }
    return $data;
}

/**
* 对查询结果集进行排序
* @access public
* @param array $list 查询结果
* @param string $field 排序的字段名
* @param array $sortby 排序类型
* asc正向排序 desc逆向排序 nat自然排序
* @return array
*/
function list_sort_by($list,$field, $sortby='asc') {
   if(is_array($list)){
       $refer = $resultSet = array();
       foreach ($list as $i => $data)
           $refer[$i] = &$data[$field];
       switch ($sortby) {
           case 'asc': // 正向排序
                asort($refer);
                break;
           case 'desc':// 逆向排序
                arsort($refer);
                break;
           case 'nat': // 自然排序
                natcasesort($refer);
                break;
       }
       foreach ( $refer as $key=> $val)
           $resultSet[] = &$list[$key];
       return $resultSet;
   }
   return false;
}

/**
 * 插件显示内容里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 */
function addons_url($url, $param = array()){
    $url        = parse_url($url);
    $case       = C('URL_CASE_INSENSITIVE');
    $addons     = $case ? parse_name($url['scheme']) : $url['scheme'];
    $controller = $case ? parse_name($url['host']) : $url['host'];
    $action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if(isset($url['query'])){
        parse_str($url['query'], $query);
        $param = array_merge($query, $param);
    }

    /* 基础参数 */
    $params = array(
        '_addons'     => $addons,
        '_controller' => $controller,
        '_action'     => $action,
    );
    $params = array_merge($params, $param); //添加额外参数

    return U('Addons/execute', $params);
}

function downImage($url, $file_path){
    //$binary = @file_get_contents($url);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $binary = curl_exec($ch);
    curl_close($ch);

    if(!$binary){
        return false;
    }

    if(@$fp = fopen($file_path, 'w+')){

        fwrite($fp, $binary);
        fclose($fp);
        return true;
    }

    return false;
}

function downImageToDB($url, $config_key, $add_db = true){
    $img_http = get_headers($url,true);
    $ext = '';
    switch($img_http['Content-Type']){
        case 'image/jpeg':
            $ext = '.jpeg';
            break;
        case 'image/gif':
            $ext = '.gif';
            break;
        case 'image/x-icon':
            $ext = '.ico';
            break;
        case 'image/png':
            $ext = '.png';
            break;
        default:
            break;
    }

    $config = C($config_key);
    if(!$config){
        E('未配置图片上传参数');
    }

    $size = $img_http['Size'];
    $rule = $config['subName'];
    $func     = $rule[0];
    $param    = (array)$rule[1];

    $sub_path = call_user_func_array($func, $param);

    $file_name = uniqid() . $ext;
    $file = $config['rootPath'] . $config['savePath'] . $sub_path . '/' . $file_name;
    createDir($config['rootPath'] . $config['savePath'] . $sub_path);
    if(downImage($url, $file) === false){
        return false;
    }

    $data['title'] = $file_name;
    $data['file'] = $config['savePath'] . $sub_path . '/' . $file_name;
    $data['size'] = $size;
    $data['cate'] = strtolower(array_pop(explode('_', $config_key)));
    $data['upload_date'] = time();

    if($add_db){
        $r = D('FilePic')->add($data);
        if($r === false){
            return false;
        }
        else{
            return $r;
        }
    }
    else{
        return $data;
    }
}

function createDir($dir){
        if(is_dir($dir)){
            return true;
        }

        if(mkdir($dir, 0777, true)){
            return true;
        } else {
            return false;
        }
    }

//security_filter 安全过滤器
function sf($text){
    $text = nl2br($text);
    $text = strip_tags($text);
    $text = addslashes($text);
    $text = trim($text);
    return $text;
}

//开发工具类函数 end

//前台信息展示类函数 begin
function getCateNameById($id = 0){
    $Model = D('Cate');
    return $Model->where(array('id'=>$id,))->getField('name');
}

function getCateListByType($type, $sort = ''){
    $Model = D('Cate');
    return $Model->getCateList($type, $sort);
}

function getDonateMonthlyPurpose($detail_id){
    $detail_ent = D('DonateMonthlyDetail')->getOne($detail_id);
    return getCateNameById($detail_ent['ref_id']);
}

//显示数据库存储文件标题
function showFileTitle($file_id){
    $file_pic = M('FilePic');
    $file_pic_ent = $file_pic->find($file_id);
    if($file_pic_ent){
        return $file_pic_ent['title'];
    }
    return '';
}


//自动格式化显示文件大小
function format_filesize($filesize){
    if(is_numeric($filesize)){
        $decr = 1024;
        $step = 0;
        $filesize = $filesize / $decr;
        $prefix = array('KB','MB','GB','TB','PB');
        while(($filesize / $decr) > 0.9){
            $filesize = $filesize / $decr;
            $step++;
        }
        return round($filesize,2).' '.$prefix[$step];
    } else {
        return '0';
    }
}

//检查value是否DBCont里的设置值
function checkDBContSetting($value, $get_list_fun){
    $list = Gy_Library\DBCont::$get_list_fun();
    return isset($list[$value]);
}

//让view调用DBCont里的方法
function callDBContFun($fun, $key = ''){
    if($key == ''){
        return Gy_Library\DBCont::$fun();
    }

    return Gy_Library\DBCont::$fun($key);
}

//前端万能数据库读取器
function readDBDataList($model_name, $where = '', $order = '', $limit = '', $fields = ''){
    $model_name = parse_name($model_name);
    $model = D($model_name);
    if($where){
        $model->where($where);
    }
    if(!empty($order)){
        $model->order($order);
    }
    if(!empty($limit)){
        $model->limit($limit);
    }
    if($fields){
        return $model->getField($fields, true);
    }
    return $model->select();
}

//前端万能数据库读取器
function readDBDataOne($model_name, $where = '', $fields = ''){
    $model_name = parse_name($model_name);
    $model = D($model_name);

    if($where){
        $model->where($where);
    }
    if($fields){
        $model->field($fields);
    }
    return $model->find();
}

function readDBOneField($model_name, $id, $field_name){
    return D($model_name)->getOneField($id, $field_name);
}

//获取顶层分类id
//$d：调用D函数的字符串
//$cate_id：当前分类id
function getTopParentId($d,$cate_id){
    $cate_ent = D($d)->where(array('id' => $cate_id))->find();
    if($cate_ent['pid']!='0'){
        return getTopParentId($d,$cate_ent['pid']);
    }
    else{
        return $cate_id;
    }

}

function getSecondParentId($d,$cate_id,$child_cate_id=0){
    $cate_ent = D($d)->where(array('id' => $cate_id))->find();
    if($cate_ent['pid']!='0'){
        return getSecondParentId($d,$cate_ent['pid'],$cate_id);
    }
    else{
        return $child_cate_id;
    }

}

//读取指定分类$id的所有子元素，包括自己。当$id=0时，为读取此整个model的分类元素
function readChildren($model_name, $id=0, $link_field = 'pid', $order = 'sort asc,id desc'){
    static $data_list = array();
    $model = D($model_name);
    $model->where('status=' . Gy_Library\DBCont::NORMAL_STATUS . ' and ' . $link_field . '=' . $id);
    if(!empty($order)){
        $model->order($order);
    }
    $ents = $model->select();
    if($ents){
        foreach($ents as $ent){
            readChildren($model_name, $ent['id'], $link_field, $order);
        }
    }
    $data_list[] = D($model_name)->where('status=' . Gy_Library\DBCont::NORMAL_STATUS . ' and id=' . $id)->find();
    return $data_list;
}

function displayTree($type,$tree,$current_cate_id){
    echo '<ul>';
    foreach ($tree as $toptree) {
        $url = empty($toptree['url']) ? U('/home/'.$type.'/index',array('cate_id'=>$toptree['id'])) : $toptree['url'];
        if($toptree['id']==$current_cate_id){
            echo '<li class="current"><a href="'. $url .'">'.$toptree['name'].'</a>';
        }
        else{
            echo '<li><a href="'. $url .'">'.$toptree['name'].'</a>';
        }
        $t = $toptree['_child'];
        if($t){
            displayTree($type, $t,$current_cate_id);
        }
        echo '</li>';
    }
    echo '</ul>';
}

function readContributeCateName($model_name, $id){
    $ent = D($model_name)->getOne($id);
    if($ent['pid'] != 0){
        return readContributeCateName($model_name, $ent['pid']) . '__' . $ent['name'];
    }
    else{
        return $ent['name'];
    }
}

function getContributeCate($uid=''){

    if(empty($uid)){
        foreach(Gy_Library\DBCont::getContributeModelList() as $m){
            $map['contribute'] = 1;
            $map['status'] = 1;
            $ents = D($m)->where($map)->select();
            foreach($ents as $ent){
                $contribute_cate[$m . '_' . $ent['id']] = readContributeCateName($m, $ent['id']);
            }
        }
        return $contribute_cate;
    }
    else{
        $role_arr = D('RoleUser')->where('user_id=' . $uid)->getField('role_id', true);
        foreach(\Gy_Library\DBCont::getContributeModelList() as $m){
            $map['contribute'] = 1;
            $map['status'] = 1;
            $ents = D($m)->where($map)->select();
            foreach($ents as $ent){
                $contribute_role_arr = explode(',', $ent['contribute_role']);
                if(!array_intersect($contribute_role_arr, $role_arr)){
                    continue;
                }
                $contribute_cate[$m . '_' . $ent['id']] = readContributeCateName($m, $ent['id']);
            }
        }
        return $contribute_cate;
    }

}

//如存在url，则返回url，否则返回文章的链接
function showPostUrl($url,$post_link){
    return $url?$url:$post_link;
}

function getGenderName($key){
    $gender_array = callDBContFun('getGenderList');
    return $gender_array[$key];
}

//将id序列转换成对应的名称序列
function idToNameFromModel($id_str, $model_name, $id_key, $name_key){
    $arr = explode(',', $id_str);

    $return_str = '';
    foreach($arr as $v){
        if(!$v){
            continue;
        }

        $name = M($model_name)->where(array($id_key => $v))->getField($name_key);
        $return_str .= $name . ',';
    }
    return trim($return_str, ',');
}

//将id序列转换成对应的名称序列
function idToNameFromDBCont($id_str, $function_name){
    $arr = explode(',', $id_str);

    $return_str = '';
    foreach($arr as $v){
        if(!$v){
            continue;
        }

        $name = Gy_Library\DBCont::$function_name($v);
        $return_str .= $name . ',';
    }
    return trim($return_str, ',');
}

/**
 * 配合文件上传插件使用  把file_ids转化为srcjson
 * example: $ids = '1,2'  
 *   return: [ "https:\/\/csh-pub-resp.oss-cn-shenzhen.aliyuncs.com\/Uploads\/image\/20181123\/5bf79e7860393.jpg",
 *          //有数据的时候返回showFileUrl($id)的结果
 *    ''    //没有数据时返回空字符串
 *   ];
 * @param $ids array|string file_ids
 * @return string data srcjson
 */
function fidToSrcjson($ids){
    if ($ids) {
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        $json = [];
        foreach ($ids as $id) {
            $json[] = showFileUrl($id);
        }
        return htmlentities(json_encode($json));
    }else{
        return '';
    }
}


/**
 * 裁剪字符串
 *   保证每个裁切的字符串视觉长度一致,而curLength裁剪会导致视觉长度参差不齐
 *   frontCutLength: 中文算2个字符长度，其他算1个长度
 *   curLength:      每个字符都是算一个长度
 *
 *   example1: 若字符串长度小等于$len,将会原样输出$str;
 *   frontCutLength('字符1',5)；    @return: '字符1';
 *
 *   example2: 若字符串长度大于$len
 *   frontCutLength('字符12',5)；   @return: '字...';(最后的"..."会算入$len)
 *
 *   example3: 若字符串长度大于$len，且最大长度的字符不能完整输出,则最大长度的字符会被忽略
 *   frontCutLength('1字符串',5)；  @return: '1....';("字"被省略，最后的"..."会算入$len)
 *
 * @param $str string 要截的字符串
 * @param $len int|string 裁剪的长度 按英文的长度计算
 * @return false|string
 */
function frontCutLength($str,$len){
    $gbStr=iconv('UTF-8','GBK',$str);
    $count=strlen($gbStr);
    if ($count<=$len){
        return $str;
    }
    $gbStr=mb_strcut($gbStr,0,$len-3,'GBK');

    $str=iconv('GBK','UTF-8',$gbStr);
    return $str.'...';
}

//前台信息展示类函数 end


