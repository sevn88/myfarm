<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 4:26
 * QQ:1467572213
 */

namespace app\index\controller;

use think\facade\Request;
use think\facade\Session;
use \app\index\model\Withdrawal as Withdrawal_m;

class Withdrawal extends Base
{
    public function index()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);

        $service_tx = \app\index\model\Base::get_config($name = 'service_tx');
        $this->assign('service_tx',$service_tx);

        $min_money = \app\index\model\Base::get_config($name = 'min_money');
        $this->assign('min_money',$min_money);

        if (!$user_info['bank_name'] || !$user_info['holder_name'] || !$user_info['account_number'] ){
            $is_card = 0;
        }else{
            $is_card = 1;
        }
        $this->assign('is_card',$is_card);
        return $this->fetch();
    }

    public function confirm_withdrawal()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        
        //针对vip5，vip6提现的操作
        if (intval($user_info['level'])==5 ){
            return ['code'=>0,'msg'=>lang("AccordingRegulations")];
        }
        if ( intval($user_info['level'])==6){
            return ['code'=>0,'msg'=>lang("AccordingNational")];
        }
        $start_withdrawal = \app\index\model\Base::get_config($name = 'start_withdrawal');
        $service_charge = \app\index\model\Base::get_config($name = 'service_tx');
        $min_money = \app\index\model\Base::get_config($name = 'min_money');
        if (Request::isPost()){
            $money = Request::post('money');
            $pass = Request::post('pass');
            $check_pass = Withdrawal_m::check_pass($user_id,$pass);
            if ($check_pass == 'error') return ['code'=>0,'msg'=>lang("TransactionPasswordError")];
            $check_order = Withdrawal_m::check_order($user_id);
            if ($check_order == 'error') return ['code'=>0,'msg'=>lang("TaskCompletedSubmit")];
            $check_group_order_num = Withdrawal_m::check_group_order_num($user_info);

            //***  加上关于等级的限制 start
            $check_group = Withdrawal_m::check_level_info($user_info,$money);
            if ($check_group!='success'){
                return ['code'=>0,'msg'=>$check_group];
            }
            //***  加上关于等级的限制 end

            if ($check_group_order_num == 'error') return ['code'=>0,'msg'=>lang("StillUnfinished")];
            // if (!$user_info['bank_name'] || !$user_info['holder_name'] || !$user_info['account_number'] || !$user_info['phone_number'] || !$user_info['ifsc'] || !$user_info['upi']) return ['code'=>0,'msg'=>'Please bind your bank card before you make a withdrawal'];
            
            if (!$user_info['bank_name'] || !$user_info['holder_name'] || !$user_info['account_number']  ) return ['code'=>0,'msg'=>lang("BindYourBank")];
            
            if ($money < $start_withdrawal) return ['code'=>0,'msg'=>lang("LessThanStarting")];
            if ($money > $user_info['balance']) return ['code'=>0,'msg'=>lang("BalanceInsufficient")];
            if ($user_info['finish_order']<=0) return ['code'=>0,'msg'=>lang("CashCanBeWithdrawn")];
            $confirm = Withdrawal_m::confirm_withdrawal($user_info,$money,$service_charge,$min_money);
            return $this->jsonResult($confirm, lang('success'), lang('error'));
        }
    }
}