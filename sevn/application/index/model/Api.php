<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/10/8
 * Time: 15:28
 * QQ:1467572213
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Api extends Model
{
    public static function get_goods_post($url, $data,$headers)
    {
        $data = json_encode($data);
        $cookie = '65da86c6917d44bd8f8ca9e79e19fd42';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        curl_close($ch);
        $rst = json_decode($output,true);
        return $rst;
    }

    public static function add_get_goods($data)
    {
        foreach ($data as $k => $v){
            $rs = Db::name('fd_goods_list');
            if(!$rs->where(array("get_id"=>$v['id']))->count()){
                $info['get_id'] = $v['id'];
                $info['goods_name'] = $v['goodsName'];
                $info['goods_price'] = $v['goodsPrice'];
                $info['goods_pic'] = $v['goodsPic'];
                $info['type'] = 1;
                $info['status'] = 1;
                $info['create_time'] = time();
                $add = Db::name('fd_goods_list')->insert($info);
            }

        }
        return 'is_over';

    }

}