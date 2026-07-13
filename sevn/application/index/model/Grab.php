<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 4:30
 * QQ:1467572213
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Grab extends Model
{
    public static function count_info($user_id)
    {
        $start_time = strtotime(date("Y-m-d"),time());
        $end_time = $start_time + 60 * 60 * 24;
        $today = [
            ['uid', '=', $user_id],
            ['status', '=', 1],
            ['create_time', '>', $start_time],
            ['create_time', '<', $end_time],
        ];
        $info['today'] = Db::name('fd_order')->where($today)->sum('order_earnings');

        $locked = [
            ['uid', '=', $user_id],
            ['status', '=', 3],
        ];
        $info['locked'] = Db::name('fd_order')->where($locked)->count();

        $unfinished = [
            ['uid', '=', $user_id],
            ['status', '=', 0],
        ];
        $info['unfinished'] = Db::name('fd_order')->where($unfinished)->count();
        return $info;
    }

    public static function auto_grab($user_info)
    {
        $check_order = self::check_order($user_info);
        if (!$check_order || !isset($check_order['status'])){
            return ['status'=>'error'];
        }
        if ($check_order['status'] == 'empty'){
            if ($user_info['auto_grab'] == 0){
                $set_info['auto_grab'] = 1;
                $set_info['update_time'] = time();
                $auto_grab = Db::name('fd_user')->where('id',$user_info['id'])->update($set_info);
                if ($auto_grab == true){
                    return ['status'=>'success'];
                }else{
                    return ['status'=>'error'];
                }
            }else{
                return ['status'=>'success'];
            }
        }else{
            return $check_order;
        }
    }

    public static function check_order($user_info)
    {
        $where = [
            ['uid', '=', $user_info['id']],
            ['status', 'in', [0,2,3]],
        ];
        $check_order = Db::name('fd_order')->where($where)->order('id','asc')->find();
        if (!$check_order) {
            return ['status'=>1,'data'=>['id'=>0]];
        }
        switch ($check_order['status'])
        {
            case 0:
                return ['status'=>0,'data'=>$check_order];
                break;
            case 2:
                return ['status'=>2];
                break;
            case 3:
                return ['status'=>3];
                break;
            default:
                return ['status'=>'empty'];
                break;
        }
    }

    public static function is_order($user_info)
    {
        $is_order = Db::name('fd_order')->where('uid',$user_info['id'])->where('status',0)->find();
        if ($is_order){
            return ['status'=>1,'data'=>$is_order['id']];
        }else{
            return ['status'=>0];
        }
    }

    public static function stop_grab($user_info)
    {
        if ($user_info['auto_grab'] == 1){
            $set_info['auto_grab'] = 0;
            $set_info['update_time'] = time();
            $stop_grab = Db::name('fd_user')->where('id',$user_info['id'])->update($set_info);
            if ($stop_grab == true){
                return 'success';
            }else{
                return 'error';
            }
        }else{
            return 'success';
        }
    }

    public static function check_is_over($user_info)
    {
        $max_mode = Db::name('fd_group_mode')->where('group_id',$user_info['group'])->max('odd_num');
        if ($max_mode === null){
            return 'is_over';
        }
        if ($user_info['group_order'] >= $max_mode){
            return 'is_over';
        }else{
            return 'is_not';
        }
    }

    public static function check_settle($user_id)
    {
        $check_settle = Db::name('fd_order')->where('uid',$user_id)->where('status',2)->count();
        return $check_settle;
    }

    public static function check_ing($user_id)
    {
        $check_ing = Db::name('fd_order')->where('uid',$user_id)->where('status',0)->find();
        if ($check_ing){
            return ['code'=>5,'msg'=>lang("AsSoonAs"),'data'=>$check_ing['id']];
        }
        return ['code'=>0,'msg'=>''];
    }

    public static function unfinished_order($user_info)
    {
        // $max_mode = Db::name('fd_group_mode')->where('group_id',$user_info['group'])->max('odd_num');
        // echo  Db::getLastSql();
        // if ($user_info['group_order'] == $max_mode){
        //     return 0;
        // }else{
        //     return 1;
        // }
        $group_order = Db::name('fd_group_mode')->where('group_id',$user_info['group'])->count();
        $group_order = $group_order - $user_info['group_order'];
        return $group_order;
    }

    public static function check_frozen($user_id)
    {
        $check_frozen = Db::name('fd_order')->where('uid',$user_id)->where('status',3)->find();
        if ($check_frozen){
            return 'yes';
        }else{
            return 'not';
        }
    }



}