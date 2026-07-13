<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/26
 * Time: 12:50
 * QQ:1467572213
 */

namespace app\agent\controller;

use think\facade\Request;
use think\Db;
use think\facade\Session;
use \app\agent\model\Recharge as Recharge_m;
use \app\index\model\Notify as Notify_m;
use \app\agent\model\User as User_m;

class Recharge extends Base
{
    public function recharge_list()
    {
        $keyword = Request::get('keyword');
        $order_id = Request::get("order_id");
        $agent_id = Session::get('agent_id');
        $userid = Request::get('userid');
        $usertel = Request::get('usertel');
        $status = Request::get('cid');
        if ($keyword){
            $sum = Recharge_m::sum($keyword,$agent_id,$userid,$usertel,$status,$order_id);
            $this->assign('sum',$sum);
        }
        $list = Recharge_m::recharge_list($keyword,$agent_id,$userid,$usertel,$status,$order_id);
        $recharge_type = Recharge_m::recharge_type();
        $page = $list->render();
        $agent_role = Session::get("role");
        $this->assign('agent_role',$agent_role);
        $this->assign('pages', $page);
        $this->assign('list', $list);
        $this->assign('recharge_type',$recharge_type);
        return $this->fetch();
    }
    
    public function determine(){
        $order_id = Db::name('fd_recharge')->find(input('id'));
        if (stripos($order_id['order_id'],"x")===0){
            Notify_m::shineupay($order_id['order_id']);
        
            return ['code'=>1,'msg'=>'充值成功'];            
        }else{
            return ['code'=>0,'msg'=>'只能同意手动充值的，别的等通道回调'];
        }
        
        
    }

}