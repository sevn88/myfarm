<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/6
 * Time: 10:42
 * QQ:1467572213
 */

namespace app\agent\controller;

use think\Controller;
use think\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\Log;
use \app\agent\model\Api as Api_m;

class Api extends Controller
{
    public function auto_push_order()
    {
        $auto_grab = Api_m::get_auto_grab();
        foreach ($auto_grab as $k => $v){
            $where = [
                ['uid', '=', $v['id']],
                ['status', 'in', [0,2,3]],
            ];
            $check_order = Db::name('fd_order')->where($where)->order('id','asc')->find();
            if (!$check_order && $v['group'] > 0){
                Api_m::auto_push_order($v['id']);
            }
        }
        echo 'success';
    }

    //冻结订单
    public function frozen_order()
    {
        $frozen_order = Api_m::frozen_order();
        echo $frozen_order;
    }

    //结算订单
    public function settle_order()
    {
        $settle_order = Api_m::settle_order();
        echo $settle_order;
    }

    //定时结算接口（供 cron 调用）
    public function settle_cron()
    {
        $start = microtime(true);
        try {
            $result = Api_m::settle_order();
            $elapsed = round(microtime(true) - $start, 3);

            // 解析结果统计
            $success = $fail = 0;
            if (strpos($result, '成功=') !== false) {
                preg_match('/成功=(\d+)/', $result, $s);
                preg_match('/失败=(\d+)/', $result, $f);
                $success = $s[1] ?? 0;
                $fail = $f[1] ?? 0;
            }

            Log::info('定时结算接口', compact('result', 'elapsed', 'success', 'fail'));

            return json(['code' => 0, 'msg' => $result, 'elapsed' => $elapsed]);
        } catch (\Exception $e) {
            Log::error('定时结算异常', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'elapsed' => round(microtime(true) - $start, 3),
            ]);
            return json(['code' => 1, 'msg' => '结算错误: ' . $e->getMessage()]);
        }
    }

    public function gg()
    {
        $goods = Api_m::get_max_goods();
        dump($goods);
    }

    //清除每日接单数

    public function del_order_num()
    {
        $del_order_num = Api_m::del_order_num();
        echo $del_order_num;
    }

}