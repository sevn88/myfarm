<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 0:20
 * QQ:1467572213
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Index extends Model
{
    public static function get_config($name)
    {
        $rst = Db::name('system_config')->where('name',$name)->value('value');
        return $rst;
    }

    public static function roll_list()
    {
        $roll_list = Db::name('fd_roll')
            ->where('type',1)
            ->where('status',1)
            ->order('id','desc')
            ->select();
        return $roll_list;
    }

    public static function news_list()
    {
        $news_list = Db::name('fd_roll')
            ->where('type',2)
            ->where('status',1)
            ->order('id','desc')
            ->select();
        return $news_list;
    }

}