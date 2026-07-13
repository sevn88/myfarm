<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/6/7
 * Time: 21:29
 * QQ:1467572213
 */

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\facade\Request;
use think\facade\Config;
use think\facade\Session;

class Upload extends controller
{
    public function upload()
    {
        $file = request()->file('file');
        $info = $file->move('uploads');
        $filename = $info->getSaveName();
        if ($filename){
            return ['code'=>1,'data'=>$filename];
        }else{
            return ['code'=>0];
        }
    }

}