<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/8
 * Time: 9:41
 * QQ:1467572213
 */

namespace app\agent\controller;

use think\facade\Request;
use think\facade\Session;
use \app\agent\model\Withdrawal as Withdrawal_m;
use \app\admin\model\Withdrawal as Withdrawal_admin;

class Withdrawal extends Base
{
    public function index()
    {
        $agent_id = Session::get('agent_id');
        $agent_role = Session::get("role");
        $keyword = Request::get('keyword');
        $userid = Request::get('userid');
        $usertel = Request::get('usertel');
        $status = Request::get('cid');
        $order_no = Request::get('order_no');
        $list = Withdrawal_m::index($agent_id,$keyword,$userid,$usertel,$status,$order_no);
        $withdrawal_type = Withdrawal_m::withdrawal_type();
        $page = $list->render();
        $this->assign('pages', $page);
        $this->assign('list', $list);
        $this->assign('role',$agent_role);
        $this->assign('withdrawal_type',$withdrawal_type);
        return $this->fetch();
    }

    public function withdrawal_pass()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            //调用通道一进行支付
            $res = \app\admin\model\Withdrawal::withdrawal_pass($id);
            if ($res == 'success'){
                return ['code'=>1,'msg'=>'提交成功,等待平台处理.'];
            }else{
                return ['code'=>0,'msg'=>$res];
            }

        }
    }

    public function withdrawal_pass2()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            //调用通道一进行支付
            $res = \app\admin\model\Withdrawal::withdrawal_pass2($id);
            if ($res == 'success'){
                return ['code'=>1,'msg'=>'提交成功,等待平台处理.'];
            }else{
                return ['code'=>0,'msg'=>$res];
            }

        }
    }
    public function withdrawal_pass3()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            //调用通道一进行支付
            $res =Withdrawal_admin::withdrawal_pass3($id);
            if ($res == 'success'){
                return ['code'=>1,'msg'=>'提交成功,等待平台处理.'];
            }else{
                return ['code'=>0,'msg'=>$res];
            }

        }
    }

    public function withdrawal_refuse()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $res = \app\admin\model\Withdrawal::withdrawal_refuse($id);
            switch ($res)
            {
                case 'success':
                    return ['code'=>1,'msg'=>'处理成功,款项已原路退回'];
                    break;
                case 'error':
                    return ['code'=>0,'msg'=>'处理失败'];
                    break;
                case 'error_status':
                    return ['code'=>0,'msg'=>'系统错误'];
                    break;
            }
        }
    }
}