<?php
/**
 * Agent 基础控制器
 * 提供通用的结果处理方法
 */

namespace app\agent\controller;

use think\Controller;
use think\facade\Request;
use think\facade\Session;
use \app\index\model\Base as Base_m;

class Base extends Controller
{
    public function initialize()
    {
        $is_login = Session::has('agent_id');
        if (!$is_login) {
            $this->redirect('login/index');
        } else {
            $controller = Request::controller(true);
            $this->assign('controller', $controller);
            $user_id = Session::get('agent_id');
            $user_info = Base_m::get_user_info($user_id);
            $this->assign('user_info', $user_info);
        }
    }

    /**
     * 将 Model 返回的字符串转为 JSON 响应
     * @param string $result Model 返回值
     * @param string $successMsg 成功消息
     * @param string $errorMsg 失败消息
     * @param array $extra 额外 case 映射
     * @return array
     */
    protected function jsonResult(string $result, string $successMsg = '操作成功', string $errorMsg = '操作失败', array $extra = [])
    {
        if ($result === 'success') {
            return ['code' => 1, 'msg' => $successMsg];
        }
        if (isset($extra[$result])) {
            return ['code' => 0, 'msg' => $extra[$result]];
        }
        return ['code' => 0, 'msg' => $errorMsg];
    }
}
