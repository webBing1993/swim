<?php
/**
 * 截取字符串长度
 * @author：linben
 */
function subtext($text, $length) {
    if(mb_strlen($text, 'utf8') > $length)
        return mb_substr($text, 0, $length, 'utf8').'...';
    return $text;
}

/**
 * json对象转数组，支持多维
 */
function ob2ar($obj) {
    if(is_object($obj)) {
        $obj = (array)$obj;
        $obj = ob2ar($obj);
    } elseif(is_array($obj)) {
        foreach($obj as $key => $value) {
            $obj[$key] = ob2ar($value);
        }
    }
    return $obj;
}
/**
 * 判断微信内置浏览器
 */
function is_weixin(){
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
        return true;
    }
    return false;
}
/**
 * 格式图片地址
 */
function get_image_path($data, $field='front_cover') {
    foreach($data as $key=>$value) {
        $data[$key][$field] = get_cover($value[$field], 'path');
    }

    return $data;
}
/**
 * 隐藏手机号码中间部分内容
 */
function hide_name($name) {
    if(preg_match('/^1[34578]\d{9}$/', $name)){
        $name = mb_substr($name, 0, 3)."****".mb_substr($name, 7, 4);
    }

    return $name;
}

/**
 * @param $num
 * @return string
 * 把阿拉伯数字转成中文数字
 */
function to_chinase_num($num) {
    $char = array("零","一","二","三","四","五","六","七","八","九");
    $dw = array("","十","百","千","万","亿","兆");
    $retval = "";
    $proZero = false;
    for($i = 0;$i < strlen($num);$i++) {
        if($i > 0)
            $temp = (int)(($num % pow (10,$i+1)) / pow (10,$i));
        else
            $temp = (int)($num % pow (10,1));

        if($proZero == true && $temp == 0) continue;

        if($temp == 0)
            $proZero = true;
        else
            $proZero = false;

        if($proZero) {
            if($retval == "") continue;
            $retval = $char[$temp].$retval;
        } else
            $retval = $char[$temp].$dw[$i].$retval;
    }
    if(strpos($retval,"一十") === 0) $retval = mb_substr($retval,1, mb_strlen($retval), 'UTF-8');

    return $retval;
}
/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 */
function time_format($time = NULL,$format='Y-m-d H:i'){
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}

function unlike_dir($path) {
    //先删除目录下的文件：
    $dh = opendir($path);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath=$path."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                unlike_dir($fullpath);
            }
        }
    }
}

function object2array(&$object) {
    $object =  json_decode( json_encode($object),true);
    return  $object;
}

function object2array_pre(&$object) {
    if(is_object($object)) {
        $object = (array)$object;
    } if(is_array($object)) {
        foreach($object as $key=>$value) {
            $object[$key] = object_array($value);
        }
    }
    return $object;
}

/**
 * 获取用户头像
 * @param $userid
 */
function get_header($userid){
    if(empty($userid)){
        return false;
    }
    $map = array(
//        'status' => array('egt',0),
        'userid' => $userid,
    );
    $user = \app\home\model\WechatUser::where($map)->find();
    if(empty($user['header']) && empty($user['avatar'])){
        $header = "/home/images/common/default.png";
    }elseif(empty($user['header']) && $user['avatar']){
        $header = $user['avatar'];
    }elseif($user['header'] && empty($user['avatar'])){
        $header = get_cover($user['header'], 'path');
    }else{
        $header = get_cover($user['header'], 'path');
    }

    return $header;
}
/**
 * 生成条形码
 * @param $id
 * @throws \BCGDrawException
 */
function bar_code($phone){
    /** 定义文件路径*/
    $file_dir = 'uploads/bar';
    /** 判断文件是否存在*/
    if(!is_dir($file_dir)) {
        /** 不存在生成*/
        mkdir($file_dir);
        chmod($file_dir,0777);
    }
    if (file_exists($file_dir.'/'.$phone.'.png')) {
        return '/'.$file_dir.'/'.$phone.'.png';
    }
    vendor('barcodegen.BCGcode128');
    vendor('barcodegen.BCGDrawing');
    vendor('barcodegen.BCGColor.php');
    /** 定义颜色*/
    $color_black = new \BCGColor(0, 0, 0);
    $color_white = new \BCGColor(255, 255, 255);
    $code = new \BCGcode128();
    $code->setScale(2);
    // 条形码的厚度
    $code->setThickness(40);
    // 条形码颜色
    $code->setForegroundColor($color_black);
    // 空白间隙颜色
    $code->setBackgroundColor($color_white);
    $code->setFont(0);
    /** 赋值颜色*/
    $drawing = new \BCGDrawing('', $color_white);
    /** 生成内容*/
    $code->parse($phone);
    $drawing->setBarcode($code);
    /** 存放路径*/
    $drawing->setFilename($file_dir.'/'.$phone.'.png');
    /** 渲染图片*/
    $drawing->draw();
    /** 生成图片*/
    $drawing->finish($drawing::IMG_FORMAT_PNG);
    return '/'.$file_dir.'/'.$phone.'.png';
}
/**
 * 生成条形码密文
 * @param $code
 * @return bool|string
 */
function bar_text($code){
    return $code ? substr(think_ucenter_encrypt($code, $code),-8) : false;
}

/**
 * @param $param
 * @param $default
 * @return mixed
 */
function param_to($param, $default){
    return $param ? $param : $default;
}

