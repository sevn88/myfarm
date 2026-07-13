<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/10/8
 * Time: 15:27
 * QQ:1467572213
 */

namespace app\index\controller;

use think\Controller;
use think\facade\Request;
use think\facade\Session;
use \app\index\model\Api as Api_m;

class Api extends Controller
{
    public function get_goods($num)
    {
        $url = 'http://dl.ysc123.com/apis/so/ln/agentGroup/goods/page';
        $page['goodsName'] = '';
        $page['priceMax'] = '';
        $page['priceMin'] = '';
        $page['page'] = $num;
        $page['size'] = '1000';
        $headers = array("Content-type: application/json;charset=UTF-8", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache", "token:65da86c6917d44bd8f8ca9e79e19fd42");

        $post = Api_m::get_goods_post($url,$page,$headers);
        if ($post['code'] == 10000 && !empty($post['data']['records'])){
            $add_get_goods = Api_m::add_get_goods($post['data']['records']);
            if ($add_get_goods == 'is_over'){
                return 'ok';
            }
        }else{
            return 'no';
        }
    }

    public function loop($num)
    {
        $loop = self::get_goods($num);
        if ($loop == 'ok'){
            $num  = $num + 1;
            $this->success('采集完成','/index/api/loop/'. $num);
        }else{
            echo 'all_over';
            die;
        }
    }

}