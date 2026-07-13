<?php

use think\Db;

function is_mobile($tel){
    if(preg_match("/^1[345789]{1}\d{9}$/",$tel)){
        return true;
    }else{
        return false;
    }
}

/*
 * 检查图片是不是bases64编码的
 */
function is_image_base64($base64) {
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)){
        return true;
    }else{
        return false;
    }
}

function check_pic($dir,$type_img){
    $new_files = $dir.date("YmdHis"). '-' . rand(0,9999999) . "{$type_img}";
    if(!file_exists($new_files))
        return $new_files;
    else
        return check_pic($dir,$type_img);  
}

/**
 * 获取数组中的某一列
 * @param array $arr 数组
 * @param string $key_name  列名
 * @return array  返回那一列的数组
 */
function get_arr_column($arr, $key_name)
{
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[] = $val[$key_name];        
	}
	return $arr2;
}

//保留两位小数
function tow_float($number){
    return (floor($number * 100) / 100); 
}

//生成订单号
function getSn($head='')
{
    @date_default_timezone_set("PRC");
    $order_id_main = date('YmdHis') . mt_rand(1000, 9999);
    //唯一订单号码（YYMMDDHHIISSNNN）
    $osn = $head.substr($order_id_main,2); //生成订单号
    return $osn;
}

/**
 * 修改本地配置文件
 *
 * @param array $name   ['配置名']
 * @param array $value  ['参数']
 * @return void
 */
function setconfig($name, $value)
{
    if (is_array($name) and is_array($value)) {
        for ($i = 0; $i < count($name); $i++) {
            $names[$i] = '/\'' . $name[$i] . '\'(.*?),/';
            $values[$i] = "'". $name[$i]. "'". "=>" . "'".$value[$i] ."',";
        }
        $fileurl = APP_PATH . "../config/app.php";
        $string = file_get_contents($fileurl); //加载配置文件
        $string = preg_replace($names, $values, $string); // 正则查找然后替换
        file_put_contents($fileurl, $string); // 写入配置文件
        return true;
    } else {
        return false;
    }
}

/**
 * 判断当前时间是否在指定时间段之内
 * @param integer $a 起始时间
 * @param integer $b 结束时间
 * @return boolean
 */
function check_time( $a, $b)
{
    $nowtime = time();
    $start = strtotime($a.':00:00');
    $end = strtotime($b.':00:00');

    if ($nowtime >= $end || $nowtime <= $start){
        return true;
    }else{
        return false;
    }
}

//时间戳显示时间
function toDate($time = "", $p = "Y-m-d H:i:s")
{
    if ($time == "") {
        $time = time();
    }
    return date($p, $time);
}

//写日志
function wlog($f, $msg)
{
    if (is_array($msg)) {
        $msg = json_encode($msg);
    }
    $file = fopen('log/' . $f . '_' . date('Y-m-d') . '.log', 'a+');
    fwrite($file, $msg . '-----' . date("Y-m-d H:i:s") . "\r\n");
    fclose($file);
}

//隐藏手机号中间4位
function hidden_tel($str)
{
    $resstr = substr_replace($str, '****', 3, 4);
    return $resstr;
}

//跳转美化
function isMobilepretty()
{
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    }
    if (isset ($_SERVER['HTTP_VIA']))
    {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        }
    }
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        }
    }
    return false;
}
function group_num($id)
{
    $group_num = Db::name('fd_user')->where('status',1)->where('group',$id)->count();
    return $group_num;
}

//只保留字符串首尾字符，隐藏中间用*代替
function str_roll($user_name,$head = 1,$foot = 1){
    $strlen     = mb_strlen($user_name, 'utf-8');
    $firstStr     = mb_substr($user_name, 0, $head, 'utf-8');
    $lastStr     = mb_substr($user_name, -$foot, $foot, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - ($head+$foot)) . $lastStr;
}

//隐藏手机号
function news_roll($user_name,$head = 3,$foot = 3){
    $strlen     = mb_strlen($user_name, 'utf-8');
    $firstStr     = mb_substr($user_name, 0, $head, 'utf-8');
    $lastStr     = mb_substr($user_name, -$foot, $foot, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - ($head+$foot)) . $lastStr;
}

//获取上级ID用户名
function get_pid_name($pid){
    $name = Db::name('fd_user')->where('id',$pid)->value('username');
    return $name;
}

//获取弹窗图片
function get_pop($id){
    $img = Db::name('fd_pop')->where('id',$id)->value('pic');
    return $img;
}

//获取IP
function get_ip() {
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        $cip = $_SERVER['HTTP_CLIENT_IP'];
    }
    else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    else if(!empty($_SERVER["REMOTE_ADDR"])){
        $cip = $_SERVER["REMOTE_ADDR"];
    }else{
        $cip = '';
    }
    preg_match("/[\d\.]{7,15}/", $cip, $cips);
    $cip = isset($cips[0]) ? $cips[0] : 'unknown';
    unset($cips);
    return $cip;
}
//获取分组
function get_group($id){
    $group =  Db::name('fd_group')->where('id',$id)->value('name');
    if (empty($group)){
        return '未分组';
    }else{
        return $group;
    }
}

//获取分组内任务数
function get_task_num($group_id)
{
    return Db::name('fd_group_mode')->where('group_id',$group_id)->count();
}


