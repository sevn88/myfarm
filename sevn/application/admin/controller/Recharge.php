<?php
/**
 * 充值管理
 * Class Recharge
 * @package app\admin\controller
 */

namespace app\admin\controller;

use library\Controller;
use think\facade\Request;
use think\Db;
use think\facade\Session;
use \app\admin\model\Recharge as Recharge_m;
use \app\index\model\Notify as Notify_m;
use \app\index\model\Base as IndexBase;

class Recharge extends Controller
{
    /**
     * 充值列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '充值管理';
        $keyword = input('get.keyword/s','');
        $usertel = input('get.usertel/s','');
        $order_no = input('get.order_no/s','');
        $userid = input('get.userid/s','');
        $agentname = input('get.agentname/s','');
        $status = input('get.cid/s','99');
        $cid = input('get.cid/s','');
        if ($keyword){
            $sum = Recharge_m::sum($keyword, $userid, $usertel, $status, $order_no, $agentname);
            $this->assign('sum', $sum);
        }
        $list = Recharge_m::index($keyword, $userid, $usertel, $status, $order_no, $agentname);
        $recharge_type = Recharge_m::recharge_type();
        $page = $list->render();
        $this->assign('pages', $page);
        $this->assign('list', $list);
        $this->assign('recharge_type', $recharge_type);
        return $this->fetch();
    }

    /**
     * 充值接口
     * @auth true
     * @menu true
     */
    public function recharge_api()
    {
        $this->title = '充值接口';
        $list = Recharge_m::recharge_mode();
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加接口
     * @auth true
     * @menu true
     */
    public function recharge_add()
    {
        if (Request::isPost()){
            $info['name'] = Request::post('name');
            $info['status'] = Request::post('status');
            $info['create_time'] = time();
            if ($info['name'] && $info['status']){
                $add = Recharge_m::recharge_add($info);
                return $this->jsonResult($add, '添加成功', '添加失败');
            }
            return $this->error('请填写完整参数');
        }
        return $this->fetch();
    }

    /**
     * 接口启用
     * @auth true
     * @menu true
     */
    public function recharge_start()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $start = Recharge_m::recharge_status($id, 1);
            return $this->jsonResult($start, '启用成功', '启用失败');
        }
    }

    /**
     * 接口禁用
     * @auth true
     * @menu true
     */
    public function recharge_stop()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $start = Recharge_m::recharge_status($id, 2);
            return $this->jsonResult($start, '停用成功', '停用失败');
        }
    }

    /**
     * 手动充值
     */
    public function topup()
    {
        $id = input('id');
        $user_info = Db::name('fd_user')->find($id);
        $money = input('money');
        $info = [
            'order_id' => time() . mt_rand(11111, 99999),
            'uid' => $user_info['id'],
            'pid' => $user_info['pid'],
            'username' => $user_info['username'],
            'phone' => $user_info['phone'],
            'money' => $money,
            'service_charge' => $money * IndexBase::get_config('service_cz') / 100,
            'type' => 1,
            'status' => 0,
            'the' => input('img'),
            'create_time' => time(),
            'address' => input('address'),
        ];
        $add = Db::name('fd_recharge')->insert($info);
        return ['code' => 1, 'msg' => 'ok'];
    }

    /**
     * 确认充值
     */
    public function determine()
    {
        $order_id = Db::name('fd_recharge')->find(input('id'));
        Notify_m::shineupay($order_id['order_id']);
        return ['code' => 1, 'msg' => '充值成功'];
    }
}
