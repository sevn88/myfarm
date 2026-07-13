<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/30
 * Time: 22:23
 * QQ:1467572213
 */

namespace app\admin\model;

use think\Model;
use think\Db;
use think\facade\Config;
use think\facade\Session;
use \app\index\model\Base;

class Index extends Model
{
    public static function statistics($yes1,$yes2)
    {
        $s_time = $yes1;
        $e_time = $yes2;

        $reg_num = [
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['reg_num'] = Db::name('fd_user')->where($reg_num)->count();

        $c_recharge = [
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['c_recharge'] = Db::name('fd_recharge')->where($c_recharge)->sum('money');

        // $service_cz = [
        //     ['type', '=', 1],
        //     ['status', '=', 1],
        //     ['create_time', '>', $s_time],
        //     ['create_time', '<', $e_time],
        // ];
        // $info['service_cz'] = Db::name('fd_recharge')->where($service_cz)->sum('service_charge');

        $m_recharge = [
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_recharge'] = Db::name('fd_recharge')->where($m_recharge)->sum('money');
        
        $info['service_cz'] =round( ($info['c_recharge'] + $info['m_recharge'])*Base::get_config('tongji_chongzhi'),2);

        $m_withdrawal = [
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal'] = Db::name('fd_withdrawal')->where($m_withdrawal)->sum('actual_money');

        // $m_withdrawal_cost = [
        //     ['type', '=', 2],
        //     ['status', '=', 1],
        //     ['create_time', '>', $s_time],
        //     ['create_time', '<', $e_time],
        // ];
        // $info['m_withdrawal_cost'] = Db::name('fd_withdrawal')->where($m_withdrawal_cost)->sum('service_money');
        $withdrawal_times = Db::name('fd_withdrawal')->where($m_withdrawal)->count();
        $info['m_withdrawal_cost'] = round($info['m_withdrawal']*Base::get_config("tongji_tixian1") + $withdrawal_times*Base::get_config("tongji_tixian3"),2);

        $grab_num = [
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['grab_num'] = Db::name('fd_order')->where($grab_num)->count();


        $z_recharge = Db::name('fd_recharge')->where($c_recharge)->count();
        $s_recharge = Db::name('fd_recharge')->where($m_recharge)->count();

        $info['r_number'] = $z_recharge + $s_recharge;

        $info['profit'] = $info['c_recharge'] + $info['m_recharge'] - $info['m_withdrawal'] - $info['m_withdrawal_cost'] -$info['service_cz'];
        // 时间段内首冲人数的统计
        $chongzhi_xiaoyu_start=[
            ['type','=','1'],
            ['status','=','1'],
            ['create_time','<',$s_time]
        ];
        $recharge_before = Db::field('uid')->distinct(true)->table('fd_recharge')->where($chongzhi_xiaoyu_start)->select();

        $uid_recharge=[];
        foreach($recharge_before as $key => $val){
            $uid_recharge[]=$val['uid'];
        }
        $c_recharge[] =array('uid','not in',$uid_recharge);
        $recharge_peoples=Db::field('uid')->distinct(true)->table('fd_recharge')->where($c_recharge)->select();
        $info['shouchongRenshu']=count($recharge_peoples);

        return $info;
    }

}