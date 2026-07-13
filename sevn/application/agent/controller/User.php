<?php
/**
 * 会员管理
 * Class User
 * @package app\agent\controller
 */

namespace app\agent\controller;

use think\facade\Request;
use think\facade\Session;
use \app\agent\model\User as User_m;

class User extends Base
{
    public function index()
    {
        $agent_id = Session::get('agent_id');
        $keyword = Request::get('keyword');
        $userid = Request::get('userid');
        $usertel = Request::get('usertel');
        $userip = Request::get('userip');
        $level_info = User_m::level_info();
        $agent_role = Session::get('role');
        $this->assign('agent_role', $agent_role);
        $this->assign('level_info', $level_info);
        $list = User_m::index($agent_id, $keyword, $userid, $usertel, $userip);
        $page = $list->render();
        $this->assign('pages', $page);
        $this->assign('list', $list);
        User_m::day_order();
        return $this->fetch();
    }

    public function edit($id)
    {
        $agent_id = Session::get('agent_id');
        if (!$id) $this->error('参数错误');

        $level_info = User_m::level_info();
        $this->assign('level_info', $level_info);
        $user_info = User_m::user_info_not_agent($agent_id, $id);
        $this->assign('user_info', $user_info);

        if (Request::isPost()){
            $id = Request::post('id');
            $edit_info = [
                'phone' => Request::post('phone'),
                'level' => Request::post('level'),
                'update_time' => time(),
                'role' => Request::post('role'),
                'freeze' => Request::post('freeze'),
                'agent_rate' => Request::post('agent_rate'),
                'bank_name' => Request::post('bank_name'),
                'holder_name' => Request::post('holder_name'),
                'account_number' => Request::post('account_number'),
                'phone_number' => Request::post('phone_number'),
                'email' => Request::post('email'),
                'ifsc' => Request::post('ifsc'),
                'upi' => Request::post('upi'),
            ];
            $password = Request::post('password');
            $payment = Request::post('payment');
            if ($password) $edit_info['password'] = md5($password);
            if ($payment) $edit_info['payment'] = md5($payment);

            $edit = User_m::edit($agent_id, $id, $edit_info);
            return $this->jsonResult($edit, '修改成功', '修改失败');
        }
        return $this->fetch();
    }

    public function level($id)
    {
        $agent_id = Session::get('agent_id');
        if (!$id) $this->error('参数错误');
        $level_info = User_m::level_info();
        $this->assign('level_info', $level_info);
        $user_info = User_m::user_info_not_agent($agent_id, $id);
        $this->assign('user_info', $user_info);

        if (Request::isPost()){
            $id = Request::post('id');
            $info['level'] = Request::post('level');
            $info['update_time'] = time();
            $edit = User_m::edit($agent_id, $id, $info);
            return $this->jsonResult($edit, '修改成功', '修改失败');
        }
        return $this->fetch();
    }

    public function status($id)
    {
        $agent_id = Session::get('agent_id');
        if (!$id) $this->error('参数错误');

        $user_info = User_m::user_info_not_agent($agent_id, $id);
        $this->assign('user_info', $user_info);

        if (Request::isPost()){
            $id = Request::post('id');
            $info['status'] = Request::post('status');
            $info['update_time'] = time();
            if (strval($info['status']) == '2') {
                $info['session_id'] = '123456';
            }
            $edit = User_m::edit($agent_id, $id, $info);
            return $this->jsonResult($edit, '修改成功', '修改失败');
        }
        return $this->fetch();
    }

    public function add($id)
    {
        $agent_id = Session::get('agent_id');
        if (!$id) $this->error('参数错误');
        $level_info = User_m::level_info();
        $this->assign('level_info', $level_info);
        $user_info = User_m::user_info_not_agent($agent_id, $id);
        $this->assign('user_info', $user_info);

        if (Request::isPost()){
            $id = Request::post('id');
            $balance = Request::post('balance');
            $info['update_time'] = time();
            $edit = User_m::recharge($id, $balance);
            return $this->jsonResult($edit, '修改成功', '修改失败');
        }
        return $this->fetch();
    }

    public function group($id)
    {
        $agent_id = Session::get('agent_id');
        if (!$id) $this->error('参数错误');
        $level_info = User_m::level_info();
        $this->assign('level_info', $level_info);
        $user_info = User_m::user_info_not_agent($agent_id, $id);
        $this->assign('user_info', $user_info);
        $group_info = User_m::get_group_info();
        $this->assign('group_info', $group_info);

        if (Request::isPost()){
            $uid = Request::post('uid');
            $group_id = Request::post('group_id');
            $set_group = User_m::set_group($uid, $group_id);
            return $this->jsonResult($set_group, '修改分组成功', '修改分组失败');
        }
        return $this->fetch();
    }

    /**
     * 会员充值
     */
    public function recharge()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $money = Request::post('money');
            $agent_role = Session::get('role');
            if ($agent_role == 2) {
                return ['code' => 0, 'msg' => '手动加彩金失败.不具备该权限'];
            }
            $recharge = User_m::recharge($id, $money);
            return $this->jsonResult($recharge, '手动增加彩金成功', '手动加彩金失败', [
                'error_status' => '系统错误',
            ]);
        }
    }

    /**
     * 封禁/解封会员
     */
    public function edit_users_status1()
    {
        $id = input('id/d', 0);
        $status = input('status/d', 0);
        if (!$id || !$status) return $this->error('参数错误');
        $res = User_m::edit_users_status($id, $status);
        return $this->jsonResult($res, '操作成功', '操作失败');
    }
}
