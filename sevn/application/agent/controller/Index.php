<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/1
 * Time: 22:26
 * QQ:1467572213
 */
namespace app\agent\controller;

use think\facade\Request;
use think\facade\Session;

class Index extends Base
{
    public function index()
    {
        return $this->fetch();
    }

}