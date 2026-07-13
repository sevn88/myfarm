<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/24
 * Time: 21:28
 * QQ:1467572213
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Notify extends Model
{
    public static function oceanpay($data)
    {
        $recharge_info = Db::name('fd_recharge')->where('order_id',$data['merordercode'])->find();
        if ($recharge_info['status'] == 0){
            $inc_money = Db::name('fd_user')
                ->where('id',$recharge_info['uid'])
                ->setInc('balance',$recharge_info['money']);
            if ($inc_money == true){
                $recharge_status = Db::name('fd_recharge')
                    ->where('order_id',$data['merordercode'])
                    ->setField('status',1);
                if ($recharge_status == true){
                    return 'success';
                }else{
                    return 'error_status';
                }
            }else{
                return 'error_money';
            }
        }else{
            return 'error_info';
        }
    }

    public static function oceanpay_w($sign_data)
    {
        $res = Db::name('fd_withdrawal')->where('order_id',$sign_data['merissuingcode'])->setField('status',1);
        if ($res == true){
            return 'success';
        }else{
            return 'error';
        }
    }


    public static function shineupay($orderId)
    {
        $where['order_id'] = $orderId;
        $where['status'] = 0;
        $recharge_info = Db::name('fd_recharge')->where($where)->find();
        if ($recharge_info){
            $inc_money = Db::name('fd_user')
                ->where('id',$recharge_info['uid'])->setInc('balance',$recharge_info['money']);
            if ($inc_money == true){
                $recharge_status = Db::name('fd_recharge')->where($where)->setField('status',1);
                if ($recharge_status == true){
                    return 'success';
                }else{
                    return 'error';
                }
            }else{
                return 'error_money';
            }
        }else{
            return 'error_order';
        }
    }

}