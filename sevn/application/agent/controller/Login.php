<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/1
 * Time: 22:30
 * QQ:1467572213
 */

namespace app\agent\controller;

use think\Controller;
use think\facade\Request;
use think\facade\Session;
use \app\agent\model\Login as Login_m;

class Login extends Controller
{
    public function index()
    {
        if (Request::isPost()){
            $username = Request::post('username');
            $password = Request::post('password');
            $login = Login_m::index($username,$password);
            if ($login == 'error'){
                $this->error('账号或密码错误');
            }else{
                Session::set('agent_id',$login['id']);
                Session::set('role',$login['role']);
                Session::set('agent_user.username',$username);
                $this->success('登录成功', Url('index/index'));
            }
        }
        return $this->fetch();
    }

    public function logout()
    {
        Session::delete('agent_id');
        Session::delete('role');
        Session::delete('agent_user.username');
        $this->success('已安全退出', Url('login/index'));
    }


}