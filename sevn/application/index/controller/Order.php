<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 4:29
 * QQ:1467572213
 */

namespace app\index\controller;

use think\facade\Request;
use think\facade\Session;
use \app\index\model\Order as Order_m;

class Order extends Base
{
    public function index()
    {
        
        $user_id = Session::get('user_id');
        $type = Request::get('type');
        $list = Order_m::index($user_id,$type);
        
        $page = $list->render(); //分页数据
        $this->assign('pages', $page);
        $this->assign('list', $list);
        $url = Request::url();
        $this->assign('url',$url);
        return $this->fetch();
    }

    public function order_finish()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        if (Request::isPost()){
            $id = Request::post('id');
            $pass = Request::post('pass');
            if ($user_info['payment'] == md5($pass)){
                $order_finish = Order_m::order_finish($id,$user_info);

                switch ($order_finish['status'])
                {
                    case 'success_go':
                        return ['code'=>3,'msg'=>lang("Completed"),'data'=>$order_finish['data']];
                    case 'success':
                        return ['code'=>1,'msg'=>lang("Completed")];
                        break;
                    case 'error':
                        return ['code'=>0,'msg'=>lang("SystemException")];
                        break;
                    case 'error_dec':
                        return ['code'=>0,'msg'=>lang("ExceptionCodeDec")];
                        break;
                    case 'error_status':
                        return ['code'=>0,'msg'=>lang("OrderStatusError")];
                        break;
                    case 'error_money':
                        return ['code'=>2,'msg'=>lang("YourCreditLow"),'less_money'=>$order_finish["less_money"]];
                        break;
                }
            }else{
                return ['code'=>0,'msg'=>lang("TransactionPasswordError")];
            }
        }
    }

    public function detail($id = 0)
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        $this->assign('user_info',$user_info);
        $http = Request::domain();
        $this->assign('http',$http);
        if ($id){
            $info = Order_m::info($user_id,$id);
            if ($info){
                $this->assign('info',$info);
            }else{
                $this->error('Error');
            }
        }else{
            $this->error('Error');
        }
        return $this->fetch();
    }

    public function check_timeout()
    {
        if (Request::isPost()){
            $order_id = Request::post('order_id');
            $check = Order_m::check_timeout($order_id);
            switch ($check['status'])
            {
                case 1:
                    return ['code'=>1,'data'=>$check];
                    break;
                case 0:
                    return ['code'=>0];
                    break;
            }
        }
    }

    public function check_settlement()
    {
        if (Request::isPost()){
            $order_id = Request::post('order_id');
            $check = Order_m::check_settlement($order_id);
            switch ($check['status'])
            {
                case 1:
                    return ['code'=>1];
                    break;
                case 0:
                    return ['code'=>0];
                    break;
            }
        }
    }

}