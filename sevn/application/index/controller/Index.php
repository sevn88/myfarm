<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 0:20
 * QQ:1467572213
 */

namespace app\index\controller;

use think\facade\Request;
use think\facade\Session;
use \app\index\model\Index as Index_m;

class Index extends Base
{

    public function index()
    {
        $notice = Index_m::get_config($name = 'notice');
        $this->assign('notice',$notice);
        $noticeru = Index_m::get_config($name = 'noticeru');
        $this->assign('noticeru',$noticeru);
        $noticees = Index_m::get_config($name = 'noticees');
        $this->assign('noticees',$noticees);
        $popup = Index_m::get_config($name = 'popup');
        $this->assign('popup',$popup);
        $roll_list = Index_m::roll_list();
        $this->assign('roll_list',$roll_list);
        $news_list = Index_m::news_list();
        $this->assign('news_list',$news_list);
        return $this->fetch();
    }

    public function about_us()
    {
        return $this->fetch();
    }

    public function about_withdrawal()
    {
        return $this->fetch();
    }

    public function about_investment_income()
    {
        return $this->fetch();
    }

    public function about_invitation()
    {
        return $this->fetch();
    }

}