<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/24
 * Time: 15:23
 * QQ:1467572213
 */

namespace app\admin\controller;

use library\Controller;
use think\facade\Request;
use think\facade\Session;
use think\Db;
use \app\admin\model\Withdrawal as Withdrawal_m;

/**
 * 提现管理
 * Class Withdrawal
 * @package app\admin\controller
 */
class Withdrawal extends Controller
{
    /**
     * 提现列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '提现列表';
        $keyword = input('get.keyword/s','');
        $status = input('get.cid/s','99');
        $usertel = input('get.usertel/s','');
        $userid = input('get.userid/s','');
        $order_no = input('get.order_no/s','');
        $list = Withdrawal_m::index($keyword,$status,$userid,$usertel,$order_no);
        $page = $list->render();
        $withdraw_type = Withdrawal_m::withdraw_type();
        $this->assign('withdraw_type',$withdraw_type);
        $this->assign('pages', $page);
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 同意提现（通用方法，通过通道参数区分不同支付通道）
     * @auth true
     * @menu true
     */
    public function withdrawal_pass()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $channel = Request::post('channel', 1); // 默认通道1
            $method = 'withdrawal_pass' . ($channel == 1 ? '' : $channel);

            // 验证方法是否存在
            if (!method_exists(Withdrawal_m::class, $method)) {
                return ['code'=>0, 'msg'=>'无效的提现通道'];
            }

            $res = Withdrawal_m::$method($id);

            // 根据通道返回不同的提示信息
            $messages = [
                1  => '提交成功,等待平台处理.',
                2  => '提交成功,等待平台处理.',
                3  => '提交成功,等待平台处理.',
                6  => '提交成功,等待平台处理.', // sunpay
                7  => '提交成功,等待平台处理.', // fastpay
                8  => '提交成功,等待平台处理.', // lepay
                9  => '提交成功,等待平台处理.', // htpay
                10 => '提交成功',                // 虚拟支付
                12 => '提交成功',                // oenpay
                17 => '提交成功,请复制客户的信息提交到线下通道.',
                20 => '提交成功',                // inrpay
                21 => '提交成功',                // onepay
            ];
            $msg = $messages[$channel] ?? '提交成功,等待平台处理.';

            if ($res == 'success'){
                return ['code'=>1,'msg'=>$msg];
            }else{
                return ['code'=>0,'msg'=>$res];
            }
        }
    }

    /**
     * 拒绝提现
     * @auth true
     * @menu true
     */
    public function withdrawal_refuse()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $res = Withdrawal_m::withdrawal_refuse($id);
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
