<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/27
 * Time: 3:24
 * QQ:1467572213
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Details extends Model
{
    public static function recharge($user_id)
    {
        $recharge = Db::name('fd_recharge')->where('uid',$user_id)
            ->order('id', 'desc')
            ->paginate(10, false, [
                'query'=>request()->param(),
                'type' => 'page\page',
                'var_page' => 'page',
            ]);
        return $recharge;
    }

    public static function withdraw($user_id)
    {
        $recharge = Db::name('fd_withdrawal')->where('uid',$user_id)
            ->order('id', 'desc')
            ->paginate(10, false, [
                'query'=>request()->param(),
                'type' => 'page\page',
                'var_page' => 'page',
            ]);
        return $recharge;
    }

}