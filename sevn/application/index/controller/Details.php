<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/27
 * Time: 3:23
 * QQ:1467572213
 */

namespace app\index\controller;

use think\facade\Request;
use think\facade\Session;
use \app\index\model\Details as Details_m;

class Details extends Base
{
    public function index()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        $type = Request::get('type');
        switch ($type)
        {
            case 1:
                $list = Details_m::recharge($user_id);
                $page = $list->render(); //分页数据
                $this->assign('pages', $page);
                $this->assign('list', $list);
                break;
            case 2:
                $list = Details_m::withdraw($user_id);
                $page = $list->render(); //分页数据
                $this->assign('pages', $page);
                $this->assign('list', $list);
                break;
        }
        $this->assign('type',$type);
        $url = Request::url();
        $this->assign('url',$url);
        return $this->fetch();
    }

}