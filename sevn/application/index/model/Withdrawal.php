<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 4:27
 * QQ:1467572213
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Withdrawal extends Model
{
    public static function confirm_withdrawal($user_info,$money,$service_charge,$min_money)
    {
        $confirm_info['order_id'] = 'W'.time().mt_rand(11111,99999);
        $confirm_info['uid'] = $user_info['id'];
        $confirm_info['pid'] = $user_info['pid'];
        $confirm_info['username'] = $user_info['username'];
        $confirm_info['phone'] = $user_info['phone'];
        $confirm_info['apply_money'] = $money;
        $confirm_info['service_money'] = $money * $service_charge / 100;
        if ($confirm_info['service_money'] < $min_money){
            $confirm_info['service_money'] = $min_money;
        }
        $confirm_info['actual_money'] = $money - $confirm_info['service_money'];
        $confirm_info['bank_name'] = $user_info['bank_name'];
        $confirm_info['holder_name'] = $user_info['holder_name'];
        $confirm_info['account_number'] = $user_info['account_number'];
        $confirm_info['phone_number'] = $user_info['phone_number'];
        $confirm_info['email'] = $user_info['email'];
        $confirm_info['ifsc'] = $user_info['ifsc'];
        $confirm_info['upi'] = $user_info['upi'];
        $confirm_info['agent_check'] = 0;
        $confirm_info['type'] = 2;
        $confirm_info['status'] = 0;
        $confirm_info['create_time'] = time();
        $confirm_info['agent_id']=$user_info['agent_id'];
        $confirm_info['agent_username']=$user_info['agent_username'];
        $add_info = Db::name('fd_withdrawal')->insert($confirm_info);
        if ($add_info == true){
            $dec_money = self::dec_money($user_info['id'],$money,$field = 'balance');
            if ($dec_money == 'success'){
                return 'success';
            }else{
                return 'error';
            }
        }else{
            return 'error';
        }
    }

    public static function dec_money($user_id,$money,$field)
    {
        $dec_money = Db::name('fd_user')->where('id',$user_id)->setDec($field,$money);
        if ($dec_money == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function check_order($user_id)
    {
        $where = [
            ['uid', '=', $user_id],
            ['status', 'in', [0,2,3]],
        ];
        $check_order = Db::name('fd_order')->where($where)->count();
        if ($check_order > 0){
            return 'error';
        }else{
            return 'success';
        }
    }

    //判断分组内单量,全部做完才能提现
    public static function check_group_order_num($user_info)
    {
        $group_num = Db::name('fd_group_mode')->where('group_id',$user_info['group'])->count();
        if ($user_info['group_order'] < $group_num){
            return 'error';
        }else{
            return 'success';
        }
    }

    public static function check_pass($user_id,$pass)
    {
        $user_info = Db::name('fd_user')->where('id',$user_id)->find();
        if ($user_info['payment'] == md5($pass)){
            return 'success';
        }else{
            return 'error';
        }
    }
    public static function check_level_info($user_info,$money){
        $group_info = Db::name('fd_level')->where('name','SVIP'.$user_info['level'])->find();
        if ($money<$group_info['w_min']){
            return lang("YourLevelIs").' '.$group_info['name']." ".lang("withdrawMinNumber").' '.$group_info['w_min'].".".lang("CheckYourWithdrawNumber").".";
        }
        if ($money>$group_info['w_max']){
            return lang("YourLevelIs")." ".$group_info['name']." ".lang("withdrawMaxNumber")." ".$group_info['w_max'].".".lang("CheckYourWithdrawNumber").".";
        }
        if ($user_info['finish_order']<$group_info['w_order_num']){
            return lang("YourLevelIs").' '.$group_info['name']." ".lang("CompleteAtLeast")." ".$group_info['w_order_num'].".".lang("TasksBeforeWithdraw")." .";
        }
        $withdraw_num = Db::name('fd_withdrawal')->where('uid',$user_info['id'])->count();
        if ($withdraw_num>=$group_info['w_num']){
            return lang("YourLevelIs")." ".$group_info['name']." ".lang("OnlyCanWithdaw")." ".$group_info['w_num']." ".lang("times").".";
        }
        return 'success';
    }

}