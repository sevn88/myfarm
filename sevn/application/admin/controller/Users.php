<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller;

use think\Db;
use think\facade\Request;
use \app\admin\model\Users as User_m;
use library\Controller;

/**
 * 会员管理
 * Class Users
 * @package app\admin\controller
 */
class Users extends Controller
{

    /**
     * 指定当前数据表
     * @var string
     */
    protected $table = 'fd_user';

    /**
     * 会员列表
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '会员列表';
        $keyword = input('get.keyword/s','');
        $usertel = input('get.usertel/s','');
        $userid = input('get.userid/s','');
        $userip = input('get.userip/s','');
        $invite_code = input('get.invitecode/s','');

        $where = [];
        if ($keyword) $where[] = ['username', 'like', "%$keyword%"];
        if ($usertel) $where[] = ['phone', 'like', "%$usertel%"];
        if ($userid) $where[] = ['id', 'like', "%$userid%"];
        if ($userip) $where[] = ['reg_ip', 'like', "%$userip%"];
        if ($invite_code) $where[] = ['invite_code', 'like', "%$invite_code%"];

        $user_list = Db::name('fd_user')->where($where)->order('id', 'desc')
            ->paginate(10, false, ['query' => request()->param()]);
        $page = $user_list->render();
        $this->assign('pages', $page);
        $this->assign('list', $user_list);
        User_m::day_order();
        return $this->fetch();
    }

    /**
     * 添加会员
     * @auth true
     * @menu true
     */
    public function add_users()
    {
        if (Request::isPost()){
            $info['username'] = Request::post('username');
            $info['phone'] = Request::post('phone');
            $info['password'] = md5(Request::post('password'));
            $info['payment'] = md5(Request::post('payment'));
            $invite_code = Request::post('invite_code');
            $info['by_admin'] = '1';
            $add = User_m::add_users($info, $invite_code);
            return $this->jsonResult($add, '添加成功', '添加失败', ['error_use' => '账户已存在']);
        }
        return $this->fetch();
    }

    /**
     * 编辑会员
     * @auth true
     * @menu true
     */
    public function edit_users()
    {
        $id = Request::get('id');
        $info = User_m::get_user_info($id);
        $this->assign('info', $info);
        $level_info = User_m::level_select();
        $this->assign('level_info', $level_info);
        if (Request::isPost()){
            $id = Request::post('id');
            $edit_info = [
                'role' => Request::post('role'),
                'agent_rate' => Request::post('agent_rate'),
                'balance' => Request::post('balance'),
                'total' => Request::post('total'),
                'financial' => Request::post('financial'),
                'freeze' => Request::post('freeze'),
                'level' => Request::post('level'),
                'finish_order' => Request::post('finish_order'),
                'group_order' => Request::post('group_order'),
                'bank_name' => Request::post('bank_name'),
                'holder_name' => Request::post('holder_name'),
                'account_number' => Request::post('account_number'),
                'phone_number' => Request::post('phone_number'),
                'email' => Request::post('email'),
                'ifsc' => Request::post('ifsc'),
                'upi' => Request::post('upi'),
                'type' => Request::post('type'),
                'address_usdt' => Request::post('address_usdt'),
                'address_name' => Request::post('address_name'),
                'address_phone' => Request::post('address_phone'),
                'address_info' => Request::post('address_info'),
                'invite_code' => Request::post('invite_code'),
                'update_time' => time(),
            ];
            $password = Request::post('password');
            $payment = Request::post('payment');
            if ($password) {
                $edit_info['password'] = md5($password);
                $edit_info['session_id'] = '123';
            }
            if ($payment) {
                $edit_info['payment'] = md5($payment);
                $edit_info['session_id'] = '123';
            }
            $edit_users = User_m::edit_users($id, $edit_info);
            return $this->jsonResult($edit_users, '修改成功', '修改失败');
        }
        return $this->fetch();
    }

    /**
     * 删除会员
     */
    public function delete_user()
    {
        $this->applyCsrfToken();
        $id = input('post.id/d', 0);
        $res = Db::name('fd_user')->where('id', $id)->delete();
        $res ? $this->success('删除成功!') : $this->error('删除失败!');
    }

    /**
     * 封禁/解封会员
     * @auth true
     */
    public function edit_users_status()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $status = Request::post('status');
            if (!$id || !$status) return $this->error('参数错误');
            $res = User_m::edit_users_status($id, $status);
            return $this->jsonResult($res, '操作成功', '操作失败');
        }
    }

    /**
     * 会员等级调整
     * @auth true
     */
    public function user_level()
    {
        $id = Request::get('id');
        $info = User_m::get_user_info($id);
        $this->assign('info', $info);
        $level_info = User_m::level_select();
        $this->assign('level_info', $level_info);
        if (Request::isPost()){
            $id = Request::post('id');
            $edit_info['level'] = Request::post('level');
            $edit_info['update_time'] = time();
            $edit_users = User_m::user_level($id, $edit_info);
            return $this->jsonResult($edit_users, '修改成功', '修改失败');
        }
        return $this->fetch();
    }

    /**
     * 会员分组
     * @auth true
     */
    public function user_group()
    {
        $id = Request::get('id');
        $info = User_m::get_user_info($id);
        $this->assign('info', $info);
        $group_info = User_m::group_info();
        $this->assign('group_info', $group_info);
        if (Request::isPost()){
            $id = Request::post('id');
            $edit_info['group'] = Request::post('group');
            $edit_info['group_order'] = 0;
            $edit_info['update_time'] = time();
            $edit_users = User_m::user_group($id, $edit_info);
            return $this->jsonResult($edit_users, '修改成功', '修改失败');
        }
        return $this->fetch();
    }

    /**
     * 会员等级列表
     * @auth true
     * @menu true
     */
    public function level()
    {
        $list = User_m::level();
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加等级
     * @auth true
     * @menu true
     */
    public function add_level()
    {
        if (Request::isPost()){
            $info = [
                'name' => Request::post('name'),
                'money' => Request::post('money'),
                'order_num' => Request::post('order_num'),
                'w_num' => Request::post('w_num'),
                'w_min' => Request::post('w_min'),
                'w_max' => Request::post('w_max'),
                'level_day' => Request::post('level_day'),
                'w_order_num' => Request::post('w_order_num'),
                'create_time' => time(),
            ];
            $add_level = User_m::add_level($info);
            return $this->jsonResult($add_level, '添加成功', '添加失败');
        }
        return $this->fetch();
    }

    /**
     * 编辑等级
     * @auth true
     * @menu true
     */
    public function edit_level($id)
    {
        $level_info = User_m::level_info($id);
        $this->assign('info', $level_info);
        if (Request::isPost()){
            $id = Request::post('id');
            $info = [
                'name' => Request::post('name'),
                'money' => Request::post('money'),
                'order_num' => Request::post('order_num'),
                'w_num' => Request::post('w_num'),
                'w_min' => Request::post('w_min'),
                'w_max' => Request::post('w_max'),
                'level_day' => Request::post('level_day'),
                'w_order_num' => Request::post('w_order_num'),
            ];
            $edit_level = User_m::edit_level($id, $info);
            return $this->jsonResult($edit_level, '修改成功', '修改失败');
        }
        return $this->fetch();
    }

    /**
     * 删除等级
     * @auth true
     * @menu true
     */
    public function del_level()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $del_level = User_m::del_level($id);
            return $this->jsonResult($del_level, '删除成功', '删除失败');
        }
    }

    /**
     * 客服列表
     * @auth true
     * @menu true
     */
    public function cs_list()
    {
        $this->title = '客服列表';
        $where = [];
        if (input('tel/s','')) $where[] = ['tel', 'like', '%' . input('tel/s','') . '%'];
        if (input('username/s','')) $where[] = ['username', 'like', '%' . input('username/s','') . '%'];
        if (input('addtime/s','')) {
            $arr = explode(' - ', input('addtime/s',''));
            $where[] = ['addtime', 'between', [strtotime($arr[0]), strtotime($arr[1])]];
        }
        $this->_query('system_cs')->where($where)->page();
    }

    /**
     * 添加客服
     * @auth true
     * @menu true
     */
    public function add_cs()
    {
        if (request()->isPost()){
            $this->applyCsrfToken();
            $username = input('post.username/s','');
            $link = input('post.link/s','');
            $WhatsappTelegram = input('post.WhatsappTelegram/d', 0);
            $time = input('post.time');
            $arr = explode('-', $time);
            $btime = substr($arr[0], 0, 5);
            $etime = substr($arr[1], 1, 5);
            $data = [
                'username' => $username,
                'link' => $link,
                'btime' => $btime,
                'etime' => $etime,
                'addtime' => time(),
                'WhatsappTelegram' => $WhatsappTelegram,
            ];
            $res = db('xy_cs')->insert($data);
            $res ? $this->success('添加成功') : $this->error('添加失败，请刷新再试');
        }
        return $this->fetch();
    }

    /**
     * 客服封禁/解封
     * @auth true
     */
    public function edit_cs_status()
    {
        $this->applyCsrfToken();
        $this->_save('system_cs', ['status' => input('post.status/d', 1)]);
    }

    /**
     * 编辑客服信息
     * @auth true
     * @menu true
     */
    public function edit_cs()
    {
        if (request()->isPost()){
            $this->applyCsrfToken();
            $id = input('post.id/d', 0);
            $username = input('post.username/s','');
            $link = input('post.link/s','');
            $WhatsappTelegram = input('post.WhatsappTelegram/d', 0);
            $time = input('post.time');
            $arr = explode('-', $time);
            $btime = substr($arr[0], 0, 5);
            $etime = substr($arr[1], 1, 5);
            $data = [
                'WhatsappTelegram' => $WhatsappTelegram,
                'username' => $username,
                'link' => $link,
                'btime' => $btime,
                'etime' => $etime,
            ];
            $res = db('system_cs')->where('id', $id)->update($data);
            $res !== false ? $this->success('编辑成功') : $this->error('编辑失败，请刷新再试');
        }
        $id = input('id/d', 0);
        $this->list = db('system_cs')->find($id);
        return $this->fetch();
    }

    /**
     * 会员充值
     * @auth true
     * @menu true
     */
    public function recharge()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $money = Request::post('money');
            $recharge = User_m::recharge($id, $money);
            return $this->jsonResult($recharge, '手动充值成功', '手动充值失败', ['error_status' => '系统错误']);
        }
    }

    /**
     * 用户树形（调试中，已注释）
     * @auth true
     * @menu true
     */
    public function user_tree(Request $request)
    {
        $data = $this->getUserTreelist();
        // TODO: 调试代码待删除
        // var_dump($data);die();
        $this->assign('list', json_encode($data));
        return $this->fetch();
    }

    /**
     * 递归获取用户树
     */
    public function getUserTreelist($pid = 0, &$result = array())
    {
        $arr = db('fd_user')->where('pid', (int)$pid)->field('id,username')->select();
        if (empty($arr)) return array();
        foreach ($arr as $cm) {
            $thisArr = &$result[];
            $cm["children"] = $this->getUserTreelist((int)$cm["id"], $thisArr);
            $thisArr = $cm;
        }
        return $result;
    }

    /**
     * 客服列表-新
     */
    public function customer_tree(Request $request)
    {
        $this->title = '客服列表-新';
        $where = [];
        if (input('tel/s','')) $where[] = ['tel', 'like', '%' . input('tel/s','') . '%'];
        if (input('username/s','')) $where[] = ['username', 'like', '%' . input('username/s','') . '%'];
        if (input('addtime/s','')) {
            $arr = explode(' - ', input('addtime/s',''));
            $where[] = ['addtime', 'between', [strtotime($arr[0]), strtotime($arr[1])]];
        }
        $this->_query('fd_cs')->where($where)->page();
    }

    /**
     * 编辑客服状态-新
     */
    public function edit_customer_status(Request $request)
    {
        $this->applyCsrfToken();
        $this->_save('fd_cs', ['status' => input('post.status/d', 1)]);
    }

    /**
     * 删除客服-新
     */
    public function del_customer()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $del = User_m::del_customer($id);
            return $this->jsonResult($del, '删除成功', '删除失败');
        }
    }

    /**
     * 编辑客服-新
     */
    public function edit_customer()
    {
        if (request()->isPost()){
            $this->applyCsrfToken();
            $id = input('post.id/d', 0);
            $name = input('post.name/s','');
            $link = input('post.link/s','');
            $WhatsappTelegram = input('post.WhatsappTelegram/d', 0);
            $time = input('post.time/s','');
            $timeArr = explode("-", $time);
            $stime = substr(trim($timeArr[0]), 0, 5);
            $etime = substr(trim($timeArr[1]), 0, 5);
            $data = [
                'name' => $name,
                'link' => $link,
                'WhatsappTelegram' => $WhatsappTelegram,
                'btime' => $stime,
                'etime' => $etime,
            ];
            $res = db('fd_cs')->where('id', $id)->update($data);
            $res !== false ? $this->success('编辑成功') : $this->error('编辑失败，请刷新再试');
        }
        $id = input('id/d', 0);
        $this->list = db('fd_cs')->find($id);
        return $this->fetch();
    }

    /**
     * 添加客服-新
     * @auth true
     * @menu true
     */
    public function add_customer()
    {
        if (request()->isPost()){
            $this->applyCsrfToken();
            $username = input('post.username/s','');
            $uid = input('post.uid/d', 0);
            $role = input('post.role/d', 1);
            $name = input('post.name/s','');
            $link = input('post.link/s','');
            $WhatsappTelegram = input('post.WhatsappTelegram/d', 0);
            $time = input('post.time/s','');
            $timeArr = explode("-", $time);
            $stime = substr(trim($timeArr[0]), 0, 5);
            $etime = substr(trim($timeArr[1]), 0, 5);
            $data = [
                'username' => $username,
                'uid' => $uid,
                'role' => $role,
                'name' => $name,
                'WhatsappTelegram' => $WhatsappTelegram,
                'link' => $link,
                'btime' => $stime,
                'etime' => $etime,
                'status' => 1,
                'addtime' => time(),
            ];
            $res = db('fd_cs')->insert($data);
            $res ? $this->success('添加成功') : $this->error('添加失败，请刷新再试');
        }
        return $this->fetch();
    }

    /**
     * 根据用户名查询用户信息
     */
    public function get_userinfo()
    {
        if (request()->isPost()){
            $username = input('post.username/s','');
            if ($username) {
                $info = Db::name('fd_user')->where('username', $username)->find();
                if ($info['id']) {
                    return ['code' => 1, 'msg' => '查找成功', 'data' => $info];
                }
            }
        }
        return ['code' => 0, 'msg' => '查询失败'];
    }
}
