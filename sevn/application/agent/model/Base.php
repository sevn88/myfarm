<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 22:16
 * QQ:1467572213
 */

namespace app\agent\model;

use think\Model;
use think\Db;
use think\facade\Request;
use think\facade\Config;
use think\facade\Session;

class Base extends Model
{
    public static function get_user_info($user_id)
    {
        $user_info = Db::name('fd_user')->where('id',$user_id)->find();
        return $user_info;
    }

    public static function get_config($name)
    {
        $rst = Db::name('system_config')->where('name',$name)->value('value');
        return $rst;
    }

}