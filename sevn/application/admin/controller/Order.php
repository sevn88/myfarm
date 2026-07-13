<?php
/**
 * 订单管理
 * Class Order
 * @package app\admin\controller
 */

namespace app\admin\controller;

use library\Controller;
use think\facade\Request;
use think\facade\Session;
use \app\admin\model\Order as Order_m;

class Order extends Controller
{
    /**
     * 派单列表
     * @auth true
     */
    public function index()
    {
        $this->title = '派单列表';
        $auto_grab = Order_m::index();
        $page = $auto_grab->render();
        $this->assign('pages', $page);
        $this->assign('list', $auto_grab);
        return $this->fetch();
    }

    /**
     * 在线派单
     * @auth true
     */
    public function push_order($uid)
    {
        $this->title = '在线派单';
        if (!$uid) $this->error('参数错误');
        $this->assign('uid', $uid);
        $keyword = input('get.keyword/s', '');
        $goods_list = Order_m::push_order($keyword);
        $page = $goods_list->render();
        $this->assign('pages', $page);
        $this->assign('list', $goods_list);
        return $this->fetch();
    }

    /**
     * 派出商品
     * @auth true
     */
    public function push_goods()
    {
        $this->title = '派出商品';
        if (Request::isPost()){
            $uid = Request::post('uid');
            $id = Request::post('id');
            if (!$uid || !$id) $this->error('参数错误');
            $push_goods = Order_m::push_goods($uid, $id);
            return $this->jsonResult($push_goods, '派单成功', '派单失败,请联系技术!', ['error_stop' => 'Stop Grab Error']);
        }
    }

    /**
     * 订单列表
     * @auth true
     */
    public function order_list()
    {
        $this->title = '订单列表';
        $keyword = Request::get('keyword');
        $userid = Request::get('userid');
        $userphone = Request::get('userphone');
        $list = Order_m::order_list($keyword, $userid, $userphone);
        $page = $list->render();
        $this->assign('pages', $page);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 订单续期
     * @auth true
     */
    public function order_rollover()
    {
        $this->title = '订单续期';
        if (Request::isPost()) {
            $id = Request::post('id');
            $hour = Request::post('hour');
            $order_rollover = Order_m::order_rollover($id, $hour);
            return $this->jsonResult($order_rollover, '续期成功', '系统错误');
        }
    }

    /**
     * 订单解冻
     * @auth true
     */
    public function order_release()
    {
        $this->title = '订单解冻';
        if (Request::isPost()){
            $id = Request::post('id');
            $order_release = Order_m::order_release($id);
            return $this->jsonResult($order_release, '解冻成功', '系统错误');
        }
    }

    /**
     * 模式分组列表
     * @auth true
     */
    public function group_list()
    {
        $this->title = '模式分组';
        $keyword = Request::get('keyword');
        $list = Order_m::group_list($keyword);
        $page = $list->render();
        $this->assign('pages', $page);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加分组
     * @auth true
     */
    public function group_add()
    {
        $this->title = '添加分组';
        if (Request::isPost()) {
            $info['name'] = Request::post('name');
            $pay = Request::post('pay');
            if ($pay) $info['pay'] = implode(',', $pay);
            $info['create_time'] = time();
            $add = Order_m::group_add($info);
            return $this->jsonResult($add, '添加成功', '添加失败', [
                'error_role' => '该用户还不是代理',
                'not_user' => 'ID输入错误,该用户不存在!',
            ]);
        }
        return $this->fetch();
    }

    /**
     * 分组编辑
     * @auth true
     */
    public function group_edit()
    {
        $this->title = '分组编辑';
        $id = Request::get('id');
        $group_info = Order_m::group_info($id);
        $this->assign('group_info', $group_info);
        if (Request::isPost()){
            $group_id = Request::post('id');
            $info['name'] = Request::post('name');
            $pay = Request::post('pay');
            if ($pay) $info['pay'] = implode(',', $pay);
            $info['update_time'] = time();
            $group_edit = Order_m::group_edit($group_id, $info);
            return $this->jsonResult($group_edit, '修改成功', '修改失败');
        }
        return $this->fetch();
    }

    /**
     * 分组用户
     * @auth true
     */
    public function group_user()
    {
        $this->title = '分组用户';
        if (Request::isPost()){
            $group_id = Request::post('group_id');
            $type = Request::post('type');
            $data = json_decode(Request::post('data'), true);
            foreach ($data as $k => $v) {
                $add_user = Order_m::group_user($group_id, $type, $v['value']);
            }
            if ($add_user == 'success') return ['code' => 1, 'msg' => '修改成功'];
        }
        $id = Request::get('id');
        if ($id) {
            $this->assign('id', $id);
            $group_info = Order_m::group_info($id);
            $this->assign('group_info', $group_info);
        } else {
            $this->error('参数错误11');
        }
        return $this->fetch();
    }

    /**
     * 分组信息（AJAX）
     * @auth true
     */
    public function get_pid()
    {
        $this->title = '分组信息';
        if (Request::isPost()){
            $group_id = Request::post('group_id');
            $get_pid_z = Order_m::get_pid_z();
            $z = [];
            foreach ($get_pid_z as $k => $v) {
                $z[] = ['value' => $v['id'], 'title' => $v['username']];
            }
            $get_pid_y = Order_m::get_pid_y($group_id);
            $y = [];
            foreach ($get_pid_y as $k => $v) {
                $y[] = [$v['id']];
            }
            return ['z' => $z, 'y' => $y];
        }
    }

    /**
     * 编辑模板
     * @auth true
     */
    public function group_mode()
    {
        $this->title = '编辑模板';
        $level_info = Order_m::level_info();
        $this->assign('level_info', $level_info);
        $my_group = Order_m::group_select();
        $this->assign('my_group', $my_group);
        $pop_pic_list = Order_m::pop_pic_list(null);
        $this->assign('pop_pic_list', $pop_pic_list);
        $id = Request::get('id');
        if ($id) {
            $group_info = Order_m::group_info($id);
            $this->assign('group_info', $group_info);
            $group_mode = Order_m::group_mode($id);
            if ($group_mode) $this->assign('group_mode', $group_mode);
        } else {
            $this->error('参数错误');
        }
        return $this->fetch();
    }

    /**
     * 保存模板
     * @auth true
     */
    public function mode_save()
    {
        $this->title = '保存模板';
        $level_info = Order_m::level_info();
        $this->assign('level_info', $level_info);
        $my_group = Order_m::group_select();
        $this->assign('my_group', $my_group);
        if (Request::isPost()){
            $mode = [
                'aid' => Request::post('aid'),
                'group_id' => Request::post('group_id'),
                'grab_type' => Request::post('grab_type'),
                'is_windows' => Request::post('is_windows') ?: 0,
                'windows_img' => Request::post('windows_img'),
                'is_level' => Request::post('is_level') ?: 0,
                'level' => Request::post('level'),
                'is_group' => Request::post('is_group') ?: 0,
                'group' => Request::post('group'),
                'odd_num' => Request::post('odd_num'),
                'pay_mode' => Request::post('pay_mode'),
                'pay_value' => Request::post('pay_value'),
                'rand_num' => Request::post('rand_num'),
                'rand_bili' => Request::post('rand_bili'),
                'addition' => Request::post('addition'),
                'create_time' => time(),
            ];
            $mode_save = Order_m::mode_save($mode);
            return $this->jsonResult($mode_save, '保存成功', '保存失败');
        }
    }

    /**
     * 删除模板
     * @auth true
     */
    public function del_mode()
    {
        $this->title = '删除模板';
        if (Request::isPost()){
            $id = Request::post('id');
            $del_mode = Order_m::del_mode($id);
            return $this->jsonResult($del_mode, '删除成功', '删除失败');
        }
    }

    /**
     * 删除分组
     * @auth true
     */
    public function group_del()
    {
        $this->title = '删除分组';
        if (Request::isPost()){
            $id = Request::post('id');
            $group_del = Order_m::group_del($id);
            return $this->jsonResult($group_del, '删除成功', '删除失败', [
                'error_ues' => '还有用户在该分组,请清除后再删除.',
            ]);
        }
    }

    /**
     * 弹窗图片列表
     * @auth true
     */
    public function pop_pic_list()
    {
        $this->title = '弹窗图片';
        $keyword = Request::get('keyword');
        $list = Order_m::pop_pic_list($keyword);
        $page = $list->render();
        $this->assign('pages', $page);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加弹窗
     * @auth true
     */
    public function pop_pic_add()
    {
        if (Request::isPost()){
            $info['name'] = Request::post('name');
            $info['pic'] = Request::post('pic');
            $info['create_time'] = time();
            $add = Order_m::pop_pic_add($info);
            return $this->jsonResult($add, '添加成功', '添加失败');
        }
        return $this->fetch();
    }

    /**
     * 删除弹窗
     * @auth true
     */
    public function pop_pic_del()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $res = Order_m::pop_pic_del($id);
            return $this->jsonResult($res, '删除成功', '删除失败');
        }
    }

    /**
     * 新增订单
     * @auth true
     */
    public function order_add()
    {
        $this->title = '新增订单';
        $oid = Request::get('oid');
        if ($oid) {
            $goods_name = trim(Request::get('goods_name'));
            $money_min = trim(Request::get('money_min'));
            $money_max = trim(Request::get('money_max'));
            $goods_list = Order_m::get_goods_list($goods_name, $money_min, $money_max);
            $page = $goods_list->render();
            $this->assign('pages', $page);
            $this->assign('list', $goods_list);
            $this->assign('oid', $oid);
            return $this->fetch();
        }
        $this->error('参数错误');
    }

    /**
     * 执行新增订单
     */
    public function order_add_run()
    {
        if (Request::isPost()) {
            $oid = Request::post('oid');
            $goods_id = explode(',', Request::post('goods_id'));
            $add = Order_m::order_add_run($oid, $goods_id);
            return $this->jsonResult($add, '添加成功', '添加失败');
        }
    }

    /**
     * 订单删除
     * @auth true
     */
    public function order_del()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            if (!$id) return ['code' => 0, 'msg' => '参数错误'];
            $del = Order_m::order_del($id);
            return $this->jsonResult($del, '删除成功', '删除失败');
        }
    }

    /**
     * 订单冻结
     * @auth true
     */
    public function order_frozen()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            if (!$id) return ['code' => 0, 'msg' => '参数错误'];
            $frozen = Order_m::order_frozen($id);
            return $this->jsonResult($frozen, '冻结成功', '冻结失败');
        }
    }
}
