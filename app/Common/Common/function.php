<?php
if(!function_exists('checkGt')){
    function checkGt($value, $gt_value){
        return $value > $gt_value;
    }
}

if(!function_exists('arrToQueryStr')){
    function arrToQueryStr($arr){
        $buff = "";
        foreach ($arr as $k => $v)
        {
                if($k != "sign"){
                        $buff .= $k . "=" . $v . "&";
                }
        }

        $buff = trim($buff, "&");
        return $buff;
    }
}

//ISO8601 GMT时间 例如”2014-12-01T12:00:00.000Z”
if(!function_exists('gmt_iso8601')) {
    function gmt_iso8601($time)
    {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTimeInterface::ATOM);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
    }
}


if(!function_exists('getOpenId')) {
    function getOpenId()
    {
        $weixin_info = session('weixin_info');

        $login_type = session('login_type');
        if ($weixin_info) {
            $open_id = $weixin_info['openid'];
            return $open_id;
        }

        if ($login_type == 'volunteer') {
            $login_volunteer = session('login_volunteer');
            return $login_volunteer['open_id'];
        }

        return '';
    }
}

if(!function_exists('is_mobile')) {
    function is_mobile()
    {
        $mobile_detect = new \Common\Util\Mobile_Detect();
        return $mobile_detect->isMobile();
    }
}

if(!function_exists('showImageByHttp')) {
    function showImageByHttp($file)
    {
        $finfo = new \finfo(FILEINFO_MIME);
        $mime = $finfo->file($file);

        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("content-type: " . $mime);

        $image = imagecreatefrompng($file);
        $image = $image === false ? imagecreatefromjpeg($file) : $image;
        $image = $image === false ? imagecreatefromgif($file) : $image;
        $image = $image === false ? imagecreatefromjpeg($file) : $image;
        imagepng($image);
        imagedestroy($image);
    }
}

if(!function_exists('addon_t')) {
    function addon_t($addon_name, $file)
    {
        $url = APP_PATH . 'Addons/' . ucfirst($addon_name) . '/View/';
        $url .= C('DEFAULT_THEME') ? C('DEFAULT_THEME') . '/' : '';
        $url .= $file . C('TMPL_TEMPLATE_SUFFIX');
        return $url;
    }
}

//判断是否是微信端的请求
if(!function_exists('is_weixin')) {
    function is_weixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }
}

if(!function_exists('is_currency')) {
    function is_currency($value)
    {
        return preg_match('/^\d+(\.\d+)?$/', $value) === 1;
    }
}

if(!function_exists('is_mobile_number')) {
    function is_mobile_number($value)
    {
        return preg_match('/^1\d{10}$/', $value) === 1;
    }
}

if(!function_exists('intConvertToArr')) {
    function intConvertToArr($i)
    {
        $i = (int)$i;
        if (!is_int($i)) {
            return false;
        }

        $str = (string)$i;
        $arr = array();
        for ($n = 0, $iMax = strlen($str); $n < $iMax; $n++) {
            $arr[] = $str[$n];
        }
        return $arr;
    }
}

//遍历$path下的所有文件
if(!function_exists('searchDir')) {
    function searchDir($path, &$data)
    {
        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                if ($file != '.' && $file != '..') {
                    searchDir($path . '/' . $file, $data);
                }
            }
            $dp->close();
        }
        if (is_file($path)) {
            $data[] = $path;
        }
    }
}

if(!function_exists('isImage')) {
    function isImage($image_file)
    {
        //获取图像信息
        $info = getimagesize($image_file);

        //检测图像合法性
        if (false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))) {
            return false;
        } else {
            return true;
        }
    }
}

//检查是否已登录
if(!function_exists('isLogin')) {
    function isLogin()
    {
        return session('?login_type');
    }
}

if(!function_exists('isPersonLogin')) {
    function isPersonLogin()
    {
        return session('?pid');
    }
}



if(!function_exists('genTokenExptime')) {
    function genTokenExptime()
    {
        return time() + C('ACTIVE_EXPTIME', null, 60 * 60 * 24);
    }
}

//登录出错处理 一般与isShowVerify 一起使用实现错误登录次数过多采用验证码的功能
if(!function_exists('loginFail')) {
    function loginFail()
    {
        if (session('?login_fail_times')) {
            $login_fail_times = session('login_fail_times');
            $login_fail_times++;
            session('login_fail_times', $login_fail_times);
        } else {
            session('login_fail_times', 1);
        }
    }
}

//是否显示验证码 一般与loginFail 一起使用实现错误登录次数过多采用验证码的功能
if(!function_exists('isShowVerify')) {
    function isShowVerify()
    {
        if (!session('?login_fail_times')) {
            return false;
        }

        if (session('login_fail_times') >= C('LOGIN_ERROR_TIMES', null, 3)) {
            return true;
        } else {
            return false;
        }
    }
}

//检查session值是否存在
if(!function_exists('isSession')) {
    function isSession($value)
    {
        if (is_string($value)) {
            return strlen($value) != 0;
        } else if (is_int($value)) {
            return true;
        } else {
            return $value ? true : false;
        }
    }
}

if(!function_exists('getModuleName')) {
    function getModuleName()
    {
        $map['name'] = MODULE_NAME;
        $map['level'] = 1;
        $title = D('node')->where($map)->getField('title');
        return $title ? $title : MODULE_NAME;
    }
}

if(!function_exists('getModuleId')) {
    function getModuleId()
    {
        $map['name'] = MODULE_NAME;
        $map['level'] = 1;
        $id = D('node')->where($map)->getField('id');
        return $id;
    }
}

if(!function_exists('getControllerName')) {
    function getControllerName()
    {
        $map['name'] = CONTROLLER_NAME;
        $map['level'] = 2;
        $map['pid'] = getModuleId();
        $title = D('node')->where($map)->getField('title');
        return $title ? $title : CONTROLLER_NAME;
    }
}

if(!function_exists('getControllerId')) {
    function getControllerId()
    {
        $map['name'] = CONTROLLER_NAME;
        $map['level'] = 2;
        $map['pid'] = getModuleId();
        $id = D('node')->where($map)->getField('id');
        return $id;
    }
}

if(!function_exists('getActionName')) {
    function getActionName()
    {
        $map['name'] = ACTION_NAME;
        $map['level'] = 3;
        $map['pid'] = getControllerId();
        $title = D('node')->where($map)->getField('title');
        return $title ? $title : ACTION_NAME;
    }
}

//检查是否是英文字母
if(!function_exists('isEnglish')) {
    function isEnglish($value)
    {
        return preg_match('/^[A-Za-z]+$/', $value) == 1;
    }
}

// 分析枚举类型配置值 格式 a:名称1,b:名称2
if(!function_exists('parse_config_attr')) {
    function parse_config_attr($string)
    {
        $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
        if (strpos($string, ':')) {
            $value = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k] = $v;
            }
        } else {
            $value = $array;
        }
        return $value;
    }
}

// 分析枚举类型字段值 格式 a:名称1,b:名称2
// $key 为要获取值得键名
if(!function_exists('parse_field_attr')) {
    function parse_field_attr($string, $key = '')
    {
        if (0 === strpos($string, ':')) {
            // 采用函数定义
            return eval('return ' . substr($string, 1) . ';');
        } elseif (0 === strpos($string, '[')) {
            // 支持读取配置参数（必须是数组类型）
            return C(substr($string, 1, -1));
        }

        $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
        if (strpos($string, ':')) {
            $value = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                if ($key != '' && $key == $k) {
                    return $v;
                }
                $value[$k] = $v;
            }

            if (strpos($key, ',') !== false) {
                $key_arr = explode(',', $key);
                $key_arr_flip = array_flip($key_arr);
                $value = array_intersect_key($value, $key_arr_flip);
                return implode(',', $value);
            }
        } else {
            $value = $array;
        }
        return $value;
    }
}

//富文本 xss 过滤
if(!function_exists('html_xss')) {
    function html_xss($data)
    {
        import('Common.Util.HtmlXss.HTMLPurifier');
        $purifier = new \HTMLPurifier();
        $data = $purifier->purify($data);
        return $data;
    }
}



/**
 * 验证身份证号
 * @param $vStr
 * @return bool
 */
if(!function_exists('isCreditNo')) {
    function isCreditNo($vStr)
    {
        $vCity = array(
            '11', '12', '13', '14', '15', '21', '22',
            '23', '31', '32', '33', '34', '35', '36',
            '37', '41', '42', '43', '44', '45', '46',
            '50', '51', '52', '53', '54', '61', '62',
            '63', '64', '65', '71', '81', '82', '91'
        );

        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;

        if (!in_array(substr($vStr, 0, 2), $vCity)) return false;

        $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
        $vLength = strlen($vStr);

        if ($vLength == 18) {
            $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
        } else {
            $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
        }

        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
        if ($vLength == 18) {
            $vSum = 0;

            for ($i = 17; $i >= 0; $i--) {
                $vSubStr = substr($vStr, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr, 11));
            }

            if ($vSum % 11 != 1) return false;
        }

        return true;
    }
}

//获取文件对象
if(!function_exists('getFile')) {
    function getFile($file_id)
    {
        $file_pic = M('FilePic');
        $file_pic_ent = $file_pic->where(array('id' => $file_id))->find();
        if (!$file_pic_ent) {
            return '';
        } else {
            return $file_pic_ent;
        }
    }
}


//展示数据库存储文件缩略图URL地址
if(!function_exists('showFileSmallUrl')) {
    function showFileSmallUrl($file_id)
    {
        $file_pic = M('FilePic');
        $file_pic_ent = $file_pic->where(array('id' => $file_id))->find();
        if ($file_pic_ent) {
            return injecCdntUrl() . UPLOAD_PATH . '/' . $file_pic_ent['small'];
        }
        return '';
    }
}

//展示数据库存储文件物理路径
if(!function_exists('showFilePath')) {
    function showFilePath($file_id)
    {
        $file_pic = M('FilePic');
        $file_pic_ent = $file_pic->find($file_id);
        if ($file_pic_ent) {
            return UPLOAD_DIR . DIRECTORY_SEPARATOR . $file_pic_ent['file'];
        }
        return '';
    }
}


if(!function_exists('showHtmlContent')) {
    function showHtmlContent($content)
    {
        return html_entity_decode($content);
    }
}

//截取内容的长度
if(!function_exists('cutLength')) {
    function cutLength($content, $len)
    {
        if (mb_strlen($content, 'utf-8') <= $len) {
            return $content;
        } else {
            return mb_substr($content, 0, $len, 'utf-8') . '......';
        }
    }
}

if(!function_exists('getAreaStrByIds')) {
    function getAreaStrByIds($ids, $name = 'cname1')
    {
        if ($ids == '') {
            return '';
        }

        $area = M('Area');
        $id_arr = explode(',', $ids);
        $area_arr = array();
        foreach ($id_arr as $id) {
            $ent = $area->where(array('id' => $id))->find();
            $area_arr[] = $ent[$name];
        }
        if (count($area_arr) > 0) {
            return join(',', $area_arr);
        } else {
            return '';
        }
    }
}

if(!function_exists('getAreaNameByID')) {
    function getAreaNameByID($id, $name = 'cname1')
    {
        $area = M('Area');
        $area_ent = $area->find($id);
        return $area_ent[$name];
    }
}

if(!function_exists('getFullAreaByID')) {
    function getFullAreaByID($id)
    {
        $area = M('Area');

        $area_ent = $area->find($id);
        if ($area_ent['level'] > 1) {
            $p_name = getFullAreaByID($area_ent['upid']);
            return $p_name . ' ' . $area_ent['cname'];
        } else {
            return $area_ent['cname'];
        }
    }
}

//通过城市名称获取到对应城市ID，返回false代表获取失败
//$area_arr 为城市名称数组
if(!function_exists('getIdByFullArea')) {

    function getIdByFullArea($area_arr){
        $area = M('Area');

        $pid = 0;
        foreach($area_arr as $k=>$v){
            $v = trim($v);
            $map['cname'] = array('like', '%' . $v . '%');
            if($pid !== 0){
                $map['upid'] = $pid;
            }
            $area_ent = $area->where($map)->order('id')->select();
            if(count((array)($area_ent)) === 0){
                return false;
            }
            $pid = $area_ent[0]['id'];
            if (count((array)($area_arr)) === 3){
                foreach ($area_ent as &$ent){
                    if ((int)$ent['level'] === $k+1) $pid = $ent['id'];
                }
            }
        }
        return $pid == 0 ? false : $pid;
    }
}

if(!function_exists('mkTempFile')) {
    function mkTempFile($path, $ext = '')
    {
        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
        $file_name = md5(time());
        if ($ext != '') {
            $file_name .= '.' . $ext;
        }
        return $path . '/' . $file_name;
    }
}

if(!function_exists('mkFile')) {
    function mkFile($file, $content)
    {
        $dir = dirname($file);
        if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        if (!file_exists($file)) {
            file_put_contents($file, $content);
        }
    }
}

if(!function_exists('sysLogs')) {
    function sysLogs($message = '未知')
    {
        $syslogs = M("Syslogs");
        $data = array();
        $ip = get_client_ip();
        $data['modulename'] = getmodulename();
        $data['actionname'] = getControllerName();
        $data['opname'] = getActionName();
        $data['message'] = $message;
        $data['userid'] = isSession(session(C('USER_AUTH_KEY'))) ? session(C('USER_AUTH_KEY')) : '0';
        $data['userip'] = $ip;
        $data['create_time'] = time();
        $syslogs->add($data);
    }
}

if(!function_exists('getClassFromDir')) {
    function getClassFromDir($path, $name_space, $parent_class_name = '')
    {
        $files = array();
        $class_list = array();
        searchDir($path, $files);

        foreach ($files as $file) {
            if (preg_match('/(\w+).class.php$/', basename($file), $matches)) {
                $class_name = $matches[1];

                $class = new \ReflectionClass($name_space . $class_name);
                if ($parent_class_name == '') {
                    $class_list[] = $class->newInstance();
                    continue;
                } else if ($class->getParentClass() !== false && $class->getParentClass()->getShortName() == $parent_class_name) {
                    $class_list[] = $class->newInstance();
                    continue;
                }
            }
        }
        return $class_list;
    }
}

if(!function_exists('getClassNameFromDir')) {
    function getClassNameFromDir($path, $name_space, $parent_class_name = '')
    {
        $files = array();
        $class_name_list = array();
        searchDir($path, $files);

        foreach ($files as $file) {
            if (preg_match('/(\w+).class.php$/', basename($file), $matches)) {
                $class_name = $matches[1];

                if ($parent_class_name == '') {
                    $class_name_list[] = $class_name;
                    continue;
                }

                $class = new \ReflectionClass($name_space . $class_name);

                if ($class->getParentClass() !== false && $class->getParentClass()->getShortName() == $parent_class_name) {
                    $class_name_list[] = $class_name;
                    continue;
                }
            }
        }
        return $class_name_list;
    }
}

//返回规则类名称
//function getRuleClass($rule){
//    return '\\Common\\Rule\\' . $rule;
//}

//返回钩子绑定的插件
if(!function_exists('getHookAddons')) {
    function getHookAddons($hook_name)
    {
        $addons_ents = D('Addons')->where(array('status' => \Gy_Library\DBCont::NORMAL_STATUS))->select();
        $return = array();
        foreach ($addons_ents as $ent) {
            $class_name = get_addon_class($ent['name']);
            $methods = get_class_methods($class_name);
            if (array_intersect($methods, array($hook_name))) {
                $return[] = $ent['name'];
            }
        }
        return $return;
    }
}

if(!function_exists('getMemberNickName')) {
    function getMemberNickName($member_id)
    {
        $member_ent = D('TeamMember')->getOne($member_id);
        return $member_ent['nick_name'] != '' ? $member_ent['nick_name'] : $member_ent['name'];
    }
}

if(!function_exists('getUserName')) {
    function getUserName($uid)
    {
        if ($uid == 0) {
            return '系统';
        }

        return D('User')->getUserName($uid);
    }
}

if(!function_exists('getUserRealName')) {
    function getUserRealName($uid)
    {
        $user_ent = D('User')->getOne($uid);
        if ($user_ent['user_type'] == 'person') {
            $profile_ent = D('PersonProfile')->getByUid($uid);
            $name = $profile_ent['real_name'];
        } else if ($user_ent['user_type'] == 'company') {
            $company_ent = D('CompanyProfile')->getByUid($uid);
            $name = $company_ent['contact'];
        }
        return $name;
    }
}

if(!function_exists('getUserRealNameByMobile')) {
    function getUserRealNameByMobile($mobile)
    {
        $ent = D('User')->where(array('telephone' => $mobile, 'user_type' => 'person'))->find();
        if ($ent) {
            return getUserRealName($ent['id']);
        } else {
            return '';
        }
    }
}

if(!function_exists('getMenu')) {
    function getMenu($type)
    {
        $menu_model = D('Menu');

        return $menu_model->getMenuList($type);
    }
}

if(!function_exists('getMenuTree')) {
    function getMenuTree($pid = 0, $type = '', $strip = 0)
    {
        $menu_model = D('Menu');

        $menu_ents = $menu_model->getMenuList($type, $pid);
        $menu_tree = array();
        foreach ($menu_ents as $ent) {
            if ($ent['id'] == $strip) {
                continue;
            }

            $menu_tree[] = $ent;
            $child_menu_ents = getMenuTree($ent['id'], $type);
            foreach ($child_menu_ents as $child_ent) {
                $menu_tree[] = $child_ent;
            }
        }
        return $menu_tree;
    }
}

if(!function_exists('get_random_str')) {
    function get_random_str($length = 10)
    {
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            switch (mt_rand(0, 2)) {
                case 0 :
                    //数字
                    $str .= mt_rand(0, 9);
                    break;
                case 1 :
                    //大写字母
                    $str .= chr(mt_rand(65, 90));
                    break;
                case 2 :
                    //小写字母
                    $str .= chr(mt_rand(97, 122));
                    break;
            }
        }
        return $str;
    }
}

if(!function_exists('generateInitPwd')) {
    function generateInitPwd($length = 8)
    {
        $str = substr(md5(time()), 0, $length);
        return $str;
    }
}

if(!function_exists('maskName')) {
    function maskName($name, $mask = '*')
    {
        $len = mb_strlen($name, 'utf-8');
        $first = mb_substr($name, 0, 1, 'utf-8');

        //名字为空，直接返回
        if ($len == 0) {
            return $name;
        }

        //只有一个字，直接返回屏蔽字符
        if ($len == 1) {
            return $mask;
        }

        //大于1个字的都显示第一个字，后面用屏蔽字符取代
        if ($len > 1) {
            return $first . str_repeat($mask, $len - 1);
        }
    }
}



//开发工具类函数 begin

//author: tider
//version: 1.0
//刷新缓存内容，显示于浏览器
//$msg 要显示的网页内容
if(!function_exists('flushWebContent')) {
    function flushWebContent($msg)
    {
        echo $msg;
        ob_flush();
        flush();
    }
}

//生成hash码
if(!function_exists('hashKey')) {
    function hashKey($para)
    {
        $str = '';
        foreach ($para as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }
        $str = substr($str, 0, strlen($str) - 1);
        return md5($str);
    }
}

//$end_date 格式 Y-m-d
if(!function_exists('genEndDateTime')) {
    function genEndDateTime($end_date)
    {
        if (!$end_date) {
            return false;
        }

        return strtotime('-1 second', strtotime('+1 day', strtotime($end_date)));
    }
}

//$year_month 格式 Y-m
if(!function_exists('genEndMonthTime')) {
    function genEndMonthTime($year_month)
    {
        if (!$year_month) {
            return false;
        }

        return strtotime('-1 second', strtotime('+1 month', strtotime($year_month)));
    }
}

//返回上个月最后一天的时间值
if(!function_exists('lastMonth')) {
    function lastMonth($time)
    {
        $t = date('Y-m', $time);
        return strtotime('-1 day', strtotime($t));
    }
}

//返回查询月第一天的时间值
if(!function_exists('monthFirstDay')) {
    function monthFirstDay($time)
    {
        $str = date('Y-m', $time);
        return strtotime($str);
    }
}

//将图片文件转换成Base64
if(!function_exists('convertImgToBase64ByFile')) {
    function convertImgToBase64ByFile($file_path)
    {
        $type = getimagesize($file_path);
        $fp = fopen($file_path, 'r');
        $file_content = chunk_split(base64_encode(fread($fp, filesize($file_path))));
        switch ($type[2]) {//判读图片类型
            case 1:
                $img_type = "gif";
                break;
            case 2:
                $img_type = "jpg";
                break;
            case 3:
                $img_type = "png";
                break;
        }
        $img = 'data:image/' . $img_type . ';base64,' . $file_content;//合成图片的base64编码
        fclose($fp);
        return $img;
    }
}

if(!function_exists('getImgByFilePath')) {
    function getImgByFilePath($file_path, $width = '')
    {
        $img_html = '<img src="' . convertImgToBase64ByFile($file_path) . '"';
        if (!empty($width)) {
            $img_html .= ' width="' . $width . 'px"';
        }
        $img_html .= '/>';
        return $img_html;
    }
}

//通过图片id获取图片，并将其转换成base64
if(!function_exists('convertImgToBase64ByFileId')) {
    function convertImgToBase64ByFileId($file_id, $prefix = '')
    {
        $file_pic = M('FilePic');
        $file_pic_ent = $file_pic->where(array('id' => $file_id))->find();

        if ($file_pic_ent['security'] == 0) {
            $path = UPLOAD_DIR;
        } else {
            $path = SECURITY_UPLOAD_DIR;
        }

        $file_name = basename(UPLOAD_DIR . '/' . $file_pic_ent['file']);

        $file_path = $path . '/' . $file_pic_ent['file'];
        if ($prefix != '') {
            $file_path = $path . '/' . str_replace($file_name, $prefix . '_' . $file_name, $file_pic_ent['file']);
        }
        return convertImgToBase64ByFile($file_path);
    }
}


//文件下载
if(!function_exists('forceDownload')) {
    function forceDownload($file, $file_name = '')
    {
        $file_name = $file_name == '' ? basename($file) : $file_name;
        if ((isset($file)) && (file_exists($file))) {
            header("Content-type: application/force-download");
            header('Content-Disposition: inline; filename="' . $file . '"');
            header("Content-Transfer-Encoding: Binary");
            header("Content-length: " . filesize($file));
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            readfile("$file");
        } else {
            echo "No file selected";
        }
    }
}

//下载用户上传的文件
if(!function_exists('downloadFile')) {
    function downloadFile($file_id)
    {
        $file_pic = M('FilePic');
        $file_pic_ent = $file_pic->where(array('id' => $file_id))->find();
        if ($file_pic_ent) {
            forceDownload(UPLOAD_DIR . '/' . $file_pic_ent['file']);
        }
    }
}




/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 */
if(!function_exists('tree_to_list')) {
    function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array())
    {
        if (is_array($tree)) {
            foreach ($tree as $key => $value) {
                $reffer = $value;
                if (isset($reffer[$child])) {
                    unset($reffer[$child]);
                    tree_to_list($value[$child], $child, $order, $list);
                }
                $list[] = $reffer;
            }
            $list = list_sort_by($list, $order, $sortby = 'asc');
        }
        return $list;
    }
}

if(!function_exists('get_addon_class')) {
    function get_addon_class($name)
    {
        $class = "Addons\\{$name}\\{$name}Addon";
        return $class;
    }
}

if(!function_exists('int_to_string')) {
    function int_to_string(&$data, $map = array('status' => array(1 => '正常', -1 => '删除', 0 => '禁用', 2 => '未审核', 3 => '草稿')))
    {
        if ($data === false || $data === null) {
            return $data;
        }
        $data = (array)$data;
        foreach ($data as $key => $row) {
            foreach ($map as $col => $pair) {
                if (isset($row[$col]) && isset($pair[$row[$col]])) {
                    $data[$key][$col . '_text'] = $pair[$row[$col]];
                }
            }
        }
        return $data;
    }
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
if(!function_exists('list_sort_by')) {
    function list_sort_by($list, $field, $sortby = 'asc')
    {
        if (is_array($list)) {
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
            foreach ($refer as $key => $val)
                $resultSet[] = &$list[$key];
            return $resultSet;
        }
        return false;
    }
}

/**
 * 插件显示内容里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 */
if(!function_exists('addons_url')) {
    function addons_url($url, $param = array())
    {
        $url = parse_url($url);
        $case = C('URL_CASE_INSENSITIVE');
        $addons = $case ? parse_name($url['scheme']) : $url['scheme'];
        $controller = $case ? parse_name($url['host']) : $url['host'];
        $action = trim($case ? strtolower($url['path']) : $url['path'], '/');

        /* 解析URL带的参数 */
        if (isset($url['query'])) {
            parse_str($url['query'], $query);
            $param = array_merge($query, $param);
        }

        /* 基础参数 */
        $params = array(
            '_addons' => $addons,
            '_controller' => $controller,
            '_action' => $action,
        );
        $params = array_merge($params, $param); //添加额外参数

        return U('Addons/execute', $params);
    }
}

if(!function_exists('downImage')) {
    function downImage($url, $file_path)
    {
        //$binary = @file_get_contents($url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $binary = curl_exec($ch);
        curl_close($ch);

        if (!$binary) {
            return false;
        }

        if (@$fp = fopen($file_path, 'w+')) {

            fwrite($fp, $binary);
            fclose($fp);
            return true;
        }

        return false;
    }
}

if(!function_exists('downImageToDB')) {
    function downImageToDB($url, $config_key, $add_db = true)
    {
        $img_http = get_headers($url, true);
        $ext = '';
        switch ($img_http['Content-Type']) {
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
        if (!$config) {
            E('未配置图片上传参数');
        }

        $size = $img_http['Size'];
        $rule = $config['subName'];
        $func = $rule[0];
        $param = (array)$rule[1];

        $sub_path = call_user_func_array($func, $param);

        $file_name = uniqid('', true) . $ext;
        $file = $config['rootPath'] . $config['savePath'] . $sub_path . '/' . $file_name;
        createDir($config['rootPath'] . $config['savePath'] . $sub_path);
        if (downImage($url, $file) === false) {
            return false;
        }

        $data['title'] = $file_name;
        $data['file'] = $config['savePath'] . $sub_path . '/' . $file_name;
        $data['size'] = $size;
        $array = explode('_', $config_key);
        $cate = array_pop($array);
        $data['cate'] = strtolower($cate);
        $data['upload_date'] = time();

        if ($add_db) {
            $r = D('FilePic')->add($data);
            if ($r === false) {
                return false;
            } else {
                return $r;
            }
        } else {
            return $data;
        }
    }
}

if(!function_exists('createDir')) {
    function createDir($dir)
    {
        if (is_dir($dir)) {
            return true;
        }

        if (mkdir($dir, 0777, true) || is_dir($dir)) {
            return true;
        } else {
            return false;
        }
    }
}

//security_filter 安全过滤器
if(!function_exists('sf')) {
    function sf($text)
    {
        $text = nl2br($text);
        $text = strip_tags($text);
        $text = addslashes($text);
        $text = trim($text);
        return $text;
    }
}

//开发工具类函数 end

//前台信息展示类函数 begin
if(!function_exists('getCateNameById')) {
    function getCateNameById($id = 0)
    {
        $Model = D('Cate');
        return $Model->where(array('id' => $id,))->getField('name');
    }
}

if(!function_exists('getCateListByType')) {
    function getCateListByType($type, $sort = '')
    {
        $Model = D('Cate');
        return $Model->getCateList($type, $sort);
    }
}

if(!function_exists('getDonateMonthlyPurpose')) {
    function getDonateMonthlyPurpose($detail_id)
    {
        $detail_ent = D('DonateMonthlyDetail')->getOne($detail_id);
        return getCateNameById($detail_ent['ref_id']);
    }
}

//自动格式化显示文件大小
if(!function_exists('format_filesize')) {
    function format_filesize($filesize)
    {
        if (is_numeric($filesize)) {
            $decr = 1024;
            $step = 0;
            $filesize = $filesize / $decr;
            $prefix = array('KB', 'MB', 'GB', 'TB', 'PB');
            while (($filesize / $decr) > 0.9) {
                $filesize = $filesize / $decr;
                $step++;
            }
            return round($filesize, 2) . ' ' . $prefix[$step];
        } else {
            return '0';
        }
    }
}

//检查value是否DBCont里的设置值
if(!function_exists('checkDBContSetting')) {
    function checkDBContSetting($value, $get_list_fun)
    {
        $list = Gy_Library\DBCont::$get_list_fun();
        return isset($list[$value]);
    }
}

//让view调用DBCont里的方法
if(!function_exists('callDBContFun')) {
    function callDBContFun($fun, $key = '')
    {
        if ($key == '') {
            return Gy_Library\DBCont::$fun();
        }

        return Gy_Library\DBCont::$fun($key);
    }
}

//前端万能数据库读取器
if(!function_exists('readDBDataList')) {
    function readDBDataList($model_name, $where = '', $order = '', $limit = '', $fields = '')
    {
        $model_name = parse_name($model_name);
        $model = D($model_name);
        if ($where) {
            $model->where($where);
        }
        if (!empty($order)) {
            $model->order($order);
        }
        if (!empty($limit)) {
            $model->limit($limit);
        }
        if ($fields) {
            return $model->getField($fields, true);
        }
        return $model->select();
    }
}


//获取顶层分类id
//$d：调用D函数的字符串
//$cate_id：当前分类id
if(!function_exists('getTopParentId')) {
    function getTopParentId($d, $cate_id)
    {
        $cate_ent = D($d)->where(array('id' => $cate_id))->find();
        if ($cate_ent['pid'] != '0') {
            return getTopParentId($d, $cate_ent['pid']);
        } else {
            return $cate_id;
        }

    }
}

if(!function_exists('getSecondParentId')) {
    function getSecondParentId($d, $cate_id, $child_cate_id = 0)
    {
        $cate_ent = D($d)->where(array('id' => $cate_id))->find();
        if ($cate_ent['pid'] != '0') {
            return getSecondParentId($d, $cate_ent['pid'], $cate_id);
        } else {
            return $child_cate_id;
        }

    }
}

//读取指定分类$id的所有子元素，包括自己。当$id=0时，为读取此整个model的分类元素
if(!function_exists('readChildren')) {
    function readChildren($model_name, $id = 0, $link_field = 'pid', $order = 'sort asc,id desc')
    {
        static $data_list = array();
        $model = D($model_name);
        $model->where('status=' . Gy_Library\DBCont::NORMAL_STATUS . ' and ' . $link_field . '=' . $id);
        if (!empty($order)) {
            $model->order($order);
        }
        $ents = $model->select();
        if ($ents) {
            foreach ($ents as $ent) {
                readChildren($model_name, $ent['id'], $link_field, $order);
            }
        }
        $data_list[] = D($model_name)->where('status=' . Gy_Library\DBCont::NORMAL_STATUS . ' and id=' . $id)->find();
        return $data_list;
    }
}

if(!function_exists('displayTree')) {
    function displayTree($type, $tree, $current_cate_id)
    {
        echo '<ul>';
        foreach ($tree as $toptree) {
            $url = empty($toptree['url']) ? U('/home/' . $type . '/index', array('cate_id' => $toptree['id'])) : $toptree['url'];
            if ($toptree['id'] == $current_cate_id) {
                echo '<li class="current"><a href="' . $url . '">' . $toptree['name'] . '</a>';
            } else {
                echo '<li><a href="' . $url . '">' . $toptree['name'] . '</a>';
            }
            $t = $toptree['_child'];
            if ($t) {
                displayTree($type, $t, $current_cate_id);
            }
            echo '</li>';
        }
        echo '</ul>';
    }
}

if(!function_exists('readContributeCateName')) {
    function readContributeCateName($model_name, $id)
    {
        $ent = D($model_name)->getOne($id);
        if ($ent['pid'] != 0) {
            return readContributeCateName($model_name, $ent['pid']) . '__' . $ent['name'];
        } else {
            return $ent['name'];
        }
    }
}

if(!function_exists('getContributeCate')) {
    function getContributeCate($uid = '')
    {

        if (empty($uid)) {
            foreach (Gy_Library\DBCont::getContributeModelList() as $m) {
                $map['contribute'] = 1;
                $map['status'] = 1;
                $ents = D($m)->where($map)->select();
                foreach ($ents as $ent) {
                    $contribute_cate[$m . '_' . $ent['id']] = readContributeCateName($m, $ent['id']);
                }
            }
            return $contribute_cate;
        } else {
            $role_arr = D('RoleUser')->where('user_id=' . $uid)->getField('role_id', true);
            foreach (\Gy_Library\DBCont::getContributeModelList() as $m) {
                $map['contribute'] = 1;
                $map['status'] = 1;
                $ents = D($m)->where($map)->select();
                foreach ($ents as $ent) {
                    $contribute_role_arr = explode(',', $ent['contribute_role']);
                    if (!array_intersect($contribute_role_arr, $role_arr)) {
                        continue;
                    }
                    $contribute_cate[$m . '_' . $ent['id']] = readContributeCateName($m, $ent['id']);
                }
            }
            return $contribute_cate;
        }

    }
}


//将id序列转换成对应的名称序列
if(!function_exists('idToNameFromModel')) {
    function idToNameFromModel($id_str, $model_name, $id_key, $name_key)
    {
        $arr = explode(',', $id_str);

        $return_str = '';
        foreach ($arr as $v) {
            if (!$v) {
                continue;
            }

            $name = M($model_name)->where(array($id_key => $v))->getField($name_key);
            $return_str .= $name . ',';
        }
        return trim($return_str, ',');
    }
}

//将id序列转换成对应的名称序列str_replace_first
if(!function_exists('idToNameFromDBCont')) {
    function idToNameFromDBCont($id_str, $function_name)
    {
        $arr = explode(',', $id_str);

        $return_str = '';
        foreach ($arr as $v) {
            if (!$v) {
                continue;
            }

            $name = Gy_Library\DBCont::$function_name($v);
            $return_str .= $name . ',';
        }
        return trim($return_str, ',');
    }
}



//前台信息展示类函数 end


