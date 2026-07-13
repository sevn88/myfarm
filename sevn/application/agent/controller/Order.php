<?php
/**
 * 订单管理
 * Class Order
 * @package app\agent\controller
 */

namespace app\agent\controller;

use think\facade\Request;
use think\facade\Session;
use \app\agent\model\Order as Order_m;

class Order extends Base
{
    public function order_list()
    {
        $agent_id = Session::get('agent_id');
        $keyword = Request::get('keyword');
        $userid = Request::get('userid');
        $agent_role = Session::get('role');
        $this->assign('agent_role', $agent_role);
        $usertel = Request::get('usertel');
        $list = Order_m::order_list($agent_id, $keyword, $userid, $usertel);
        $page = $list->render();
        $this->assign('pages', $page);
        $this->assign('list', $list);
        return $this->fetch();
    }

    // 订单续期
    public function order_rollover()
    {
        if (Request::isPost()) {
            $id = Request::post('id');
            $hour = Request::post('hour');
            $order_rollover = Order_m::order_rollover($id, $hour);
            return $this->jsonResult($order_rollover, '续期成功', '系统错误');
        }
    }

    // 订单解冻
    public function order_release()
    {
        if (Request::isPost()) {
            $id = Request::post('id');
            $order_release = Order_m::order_release($id);
            return $this->jsonResult($order_release, '解冻成功', '系统错误');
        }
    }

    // 新增订单_商品列表
    public function order_add($oid = 0)
    {
        if ($oid) {
            $goods_name = trim(Request::get('goods_name'));
            $money_min = trim(Request::get('money_min'));
            $money_max = trim(Request::get('money_max'));
            $goods_list = Order_m::get_goods_list($goods_name, $money_min, $money_max);
            $page = $goods_list->render();
            $this->assign('pages', $page);
            $this->assign('datalist', $goods_list);
            $this->assign('oid', $oid);
            return $this->fetch();
        }
        $this->error('参数错误');
    }

    public function add()
    {
        if (Request::isPost()) {
            $oid = Request::post('oid');
            $goods_id = explode(',', Request::post('goods_id'));
            $add = Order_m::add($oid, $goods_id);
            return $this->jsonResult($add, '添加成功', '添加失败');
        }
    }

    public function order_del()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $del = Order_m::order_del($id);
            return $this->jsonResult($del, '删除成功', '删除失败');
        }
    }
}
