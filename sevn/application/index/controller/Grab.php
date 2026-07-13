<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 4:30
 * QQ:1467572213
 */

namespace app\index\controller;

use library\command\Sess;
use think\facade\Request;
use think\facade\Session;
use \app\index\model\Grab as Grab_m;

class Grab extends Base
{
    public function index()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        $this->assign('user_info',$user_info);
        $count_info = Grab_m::count_info($user_id);
        $this->assign('count_info',$count_info);
        $http = Request::domain();
        $this->assign('http',$http);
        $unfinished_order = Grab_m::unfinished_order($user_info);
        $this->assign('unfinished_order',$unfinished_order);
        $day = date("Y-m-d");
        $this->assign('day',$day);
        return $this->fetch();
    }

    public function auto_grab()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        if (Request::isPost()){
            $check_frozen = Grab_m::check_frozen($user_id);
            if ($check_frozen == 'yes'){
                return ['code'=>0,'msg'=>lang("GoodsBeingProcessed")];
            }
            $check_ing = Grab_m::check_ing($user_id);
            if ($check_ing && isset($check_ing['code']) && $check_ing['code'] == 5){
                return $check_ing;
            }

            $check_is_over = Grab_m::check_is_over($user_info);
            if ($check_is_over == 'is_over'){
                return ['code'=>0,'msg'=>lang("OrderBeenCompledted")];
            }



            $check_settle = Grab_m::check_settle($user_id);
            if ($check_settle > 0){
                return ['code'=>0,'msg'=>lang("MerchantClearing")];
            }

            if ($user_info['balance'] == 0){
                return ['code'=>0,'msg'=>lang("ContactCustomerRecharge")];
            }
            if ($user_info['level'] == 0){
                return ['code'=>0,'msg'=>lang("ContactCustomerService")];
            }
            if ($user_info['group'] == 0){
                return ['code'=>0,'msg'=>lang("TaskNotRequested")];
            }else{
                $auto_grab = Grab_m::auto_grab($user_info);
                switch ($auto_grab['status'])
                {
                    case 'success':
                        return ['code'=>1,'msg'=>lang("OrderGrabbingEnabled")];
                        break;
                    case 'error':
                        return ['code'=>0,'msg'=>lang("GlobalError")];
                        break;
                    case 0:
                        return ['code'=>2,'msg'=>lang("OrdersNotCompleted"),'data'=>$auto_grab['id']];
                        break;
                    case 2:
                        return ['code'=>0,'msg'=>lang("MerchantInSettlement")];
                        break;
                    case 3:
                        return ['code'=>4,'msg'=>lang("OrderBeenFrozen")];
                        break;
                    default:
                        return ['code'=>0,'msg'=>lang("GlobalError")];
                }
            }
        }
        return ['code'=>0,'msg'=>lang("GlobalError")];
    }

    public function check_settle()
    {

    }

    public function check_order()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        if (Request::isPost()){
            $check_order = Grab_m::is_order($user_info);
            if (!$check_order || !isset($check_order['status'])) {
                return ['code'=>0,'msg'=>lang("empty")];
            }
            switch ($check_order['status'])
            {
                case 0:
                    return ['code'=>0,'msg'=>lang("empty")];
                    break;
                case 1:
                    return ['code'=>2,'data'=>$check_order['data']];
                    break;
                default:
                    return ['code'=>0,'msg'=>lang("empty")];
            }
        }
    }

    public function stop_grab()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        if (Request::isPost()){
            $stop_grab = Grab_m::stop_grab($user_info);
            switch ($stop_grab)
            {
                case 'success':
                    return ['code'=>1,'msg'=>lang("OrderStopped")];
                    break;
                case 'error':
                    return ['code'=>0,'msg'=>lang("GlobalException")];
                    break;
            }
        }
    }

}