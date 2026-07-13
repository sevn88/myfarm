<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/6
 * Time: 10:42
 * QQ:1467572213
 */

namespace app\agent\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Api extends Model
{
    public $user_balance = 1;
    public $aa = 2;

    public static function get_auto_grab()
    {
        $auto_grab = Db::name('fd_user')->where('auto_grab',1)->select();
        return $auto_grab;
    }

    public static function get_group_info($id)
    {
        $group_info = Db::name('fd_group')->where('id',$id)->find();
        return $group_info;
    }

    public static function auto_push_order($user_id)
    {
        $user_info = Base::get_user_info($user_id);

        $where['group_id'] = $user_info['group'];
        $where['odd_num'] = $user_info['group_order'] + 1;
        $group_info = Db::name('fd_group_mode')->where($where)->find();
        if ($group_info){
            switch ($group_info['grab_type'])
            {
                case 1:
//                    $money = Db::name('fd_level')->where('id',$user_info['level'])->value('money');
                    $money = $user_info['balance'];
                    $goods_info = self::get_goods($money);
                    $push_goods['order_id'] = 'A'.date('md').time().mt_rand(111,999);
                    $push_goods['aid'] = $user_info['pid'];
                    $push_goods['uid'] = $user_info['id'];
                    $push_goods['username'] = $user_info['username'];
                    $push_goods['phone'] = $user_info['phone'];
                    $push_goods['grab_type'] = $group_info['grab_type'];
                    $push_goods['goods_id'] = $goods_info['id'];
                    $push_goods['goods_name'] = $goods_info['goods_name'];
                    $goods_price = $user_info['balance'];
                    switch ($group_info['pay_mode'])
                    {
                        case 1:
                            $push_goods['goods_price'] = $goods_price;
                            $push_goods['order_commission'] = $group_info['pay_value'];
                            $push_goods['order_earnings'] = $goods_price * $group_info['pay_value'];
                            break;
                        case 2:
                            $push_goods['goods_price'] = $goods_price;
                            $push_goods['order_commission'] = 0;
                            $push_goods['order_earnings'] = $group_info['pay_value'];
                            break;
                    }
                    $push_goods['goods_pic'] = $goods_info['goods_pic'];
                    $push_goods['goods_type'] = $goods_info['type'];
                    $push_goods['sub_time'] = time() + 1 * 3600;
                    $push_goods['s_num'] = 1;
                    $push_goods['e_num'] = 1;
                    if ($group_info['is_windows'] == 1 && $push_goods['e_num'] == 1){
                        $push_goods['is_pop'] = 1;
                        $push_goods['pop_id'] = $group_info['windows_img'];
                    }else{
                        $push_goods['is_pop'] = 0;
                        $push_goods['pop_id'] = 0;
                    }
                    $push_goods['status'] = 0;
                    $push_goods['create_time'] = time();
                    $push_goods['goods_price']=round($push_goods['goods_price'],2);
                    $add_push = Db::name('fd_order')->insert($push_goods);
                    if ($add_push == true){
                        $stop_grab = Db::name('fd_user')->where('id',$user_id)->setField('auto_grab',0);
                        if ($stop_grab == true){
                            return 'success';
                        }else{
                            return 'error_stop';
                        }
                    }else{
                        return 'error';
                    }
                    break;
                case 2:
                    return false;
                    break;
                case 3:
                    $order_num =  explode(',',$group_info['addition']);
                    $num = count($order_num);
                    $i = 1;
                    foreach ($order_num as $k => $v){
                        if($i > $num) break;
                        self::batch_order($user_info,$v,$group_info,$i,$num);
                        $i++;
                    }
                    Db::name('fd_user')->where('id',$user_id)->setField('auto_grab',0);
                    break;
            }
        }
    }

    private static function batch_order($user_info,$bili,$group_info,$i,$num)
    {
        $money = $user_info['balance'] * $bili;
        $goods_info = self::get_goods($money);
        $push_goods['order_id'] = 'A'.date('md').time().mt_rand(111,999);
        $push_goods['aid'] = $user_info['pid'];
        $push_goods['uid'] = $user_info['id'];
        $push_goods['username'] = $user_info['username'];
        $push_goods['phone'] = $user_info['phone'];
        $push_goods['grab_type'] = $group_info['grab_type'];
        $push_goods['goods_id'] = $goods_info['id'];
        $push_goods['goods_name'] = $goods_info['goods_name'];
        $goods_price = $money - mt_rand(1,15);
        switch ($group_info['pay_mode'])
        {
            case 1:
                $push_goods['goods_price'] = $goods_price;
                $push_goods['order_commission'] = $group_info['pay_value'];
                $push_goods['order_earnings'] = $goods_price * $group_info['pay_value'];
                break;
            case 2:
                $push_goods['goods_price'] = $goods_price;
                $push_goods['order_commission'] = 0;
                $push_goods['order_earnings'] = $group_info['pay_value'];
                break;
        }
        $push_goods['goods_pic'] = $goods_info['goods_pic'];
        $push_goods['goods_type'] = $goods_info['type'];
        $push_goods['sub_time'] = time() + 1 * 3600;
        $push_goods['s_num'] = $i;
        $push_goods['e_num'] = $num;
        if ($group_info['is_windows'] == 1 && $push_goods['e_num'] == 1){
            $push_goods['is_pop'] = 1;
            $push_goods['pop_id'] = $group_info['windows_img'];
        }else{
            $push_goods['is_pop'] = 0;
            $push_goods['pop_id'] = 0;
        }
        $push_goods['status'] = 0;
        $push_goods['create_time'] = time();
        $push_goods['goods_price']=round($push_goods['goods_price'],2);
        Db::name('fd_order')->insert($push_goods);
    }

    public static function goods_info($id)
    {
        $goods_info = Db::name('fd_goods_list')->where('id',$id)->find();
        return $goods_info;
    }

    public static function user_balanceaa($money,$price)
    {
        static $money;
        $a = $money - $price;
        return $a;
    }

    public static function get_goods($balance)
    {
        $range_min = round($balance - $balance * 0.5,2);
        $range_max = round($balance + $balance * 0.5,2);
        $where = [
            ['goods_price', '>=', $range_min],
            ['goods_price', '<=', $range_max],
        ];
        $goods = Db::name('fd_goods_list')
            ->where($where)
            ->orderRaw('rand()')
            ->find();
        if (!$goods){
            $goods = self::get_max_goods();
        }
        return $goods;
    }

    public static function get_max_goods()
    {
        $goods = Db::name('fd_goods_list')
            ->order('goods_price','desc')
            ->limit(20)->select();
        return $goods[mt_rand(0,count($goods) - 1)];
    }

    public static function rand_goods()
    {
        $min = Db::name('fd_goods_list')->min('id');
        $max = Db::name('fd_goods_list')->max('id');
        $rand_goods = Db::name('fd_goods_list')->where('id',mt_rand($min,$max))->find();
        if ($rand_goods){
            return $rand_goods;
        }else{
            self::rand_goods();
        }
    }

    public static function frozen_order()
    {
        $now_time = time();
        $where = [
            ['status', '=', 0],
            ['sub_time', '<', $now_time],
        ];
        $order_info = Db::name('fd_order')->where($where)->select();
        if ($order_info){
            foreach ($order_info as $k => $v){
                Db::name('fd_order')->where('id',$v['id'])->setField('status',3);
            }
            return 'success';
        }else{
            return 'not_order';
        }
    }

    public static function settle_order()
    {
        $now_time = time();
        $where = [
            ['status', '=', 2],
            ['settle_time', '<=', $now_time],
        ];
        $orders = Db::name('fd_order')->where($where)->select();

        if (empty($orders)) {
            return 'no_orders';
        }

        $success_count = 0;
        $fail_count = 0;
        $results = [];

        foreach ($orders as $order) {
            // 检查用户是否有待处理的订单（状态0或3）
            $pending_count = Db::name('fd_order')
                ->where('uid', $order['uid'])
                ->where('status', 'in', [0, 3])
                ->count();

            if ($pending_count > 0) {
                $results[] = "order_{$order['id']}:skip(有待处理订单)";
                continue;
            }

            try {
                $settlement_result = self::settlement($order['id']);
                if ($settlement_result === 'success') {
                    $success_count++;
                    $results[] = "order_{$order['id']}:成功";
                } else {
                    $fail_count++;
                    $results[] = "order_{$order['id']}:失败";
                }
            } catch (\Exception $e) {
                $fail_count++;
                $results[] = "order_{$order['id']}:异常(" . $e->getMessage() . ")";
            }
        }

        return "成功={$success_count},失败={$fail_count}," . implode(',', $results);
    }

    private static function settlement($id)
    {
        try {
            $result = Db::transaction(function () use ($id) {
                $order_info = Db::name('fd_order')->where('id', $id)->find();
                if (!$order_info) {
                    throw new \Exception('订单不存在');
                }

                $balance = $order_info['goods_price'] + $order_info['order_earnings'];

                // 更新订单状态为已完成
                $orderUpdated = Db::name('fd_order')
                    ->where('id', $order_info['id'])
                    ->update(['status' => 1, 'update_time' => time()]);
                if (!$orderUpdated) {
                    throw new \Exception('更新订单状态失败');
                }

                // 原子性更新用户账户：减少冻结、增加余额和总收入
                // WHERE freeze >= goods_price 作为额外防护
                $userResult = Db::name('fd_user')
                    ->where('id', $order_info['uid'])
                    ->where('freeze', '>=', $order_info['goods_price'])
                    ->update([
                        'freeze'  => Db::raw('freeze - ' . intval($order_info['goods_price'])),
                        'balance' => Db::raw('balance + ' . intval($balance)),
                        'total'   => Db::raw('total + ' . intval($order_info['order_earnings'])),
                    ]);
                if (!$userResult) {
                    throw new \Exception('用户余额更新失败 -- 冻结金额不足');
                }

                // 检查用户是否还有待结算的订单（状态=2）
                $remaining = Db::name('fd_order')
                    ->where('uid', $order_info['uid'])
                    ->where('status', 2)
                    ->count();

                if ($remaining == 0) {
                    // 所有订单已结算，更新统计计数器
                    Db::name('fd_user')
                        ->where('id', $order_info['uid'])
                        ->update([
                            'finish_order' => Db::raw('finish_order + 1'),
                            'group_order'  => Db::raw('group_order + 1'),
                            'day_o'        => Db::raw('day_o + 1'),
                        ]);
                }

                return true;
            });

            return $result ? 'success' : 'error';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    public static function del_order_num()
    {
        $user = Db::name('fd_user')->where('status',1)->select();
        foreach ($user as $k => $v){
            Db::name('fd_user')->where('id',$v['id'])->setField('day_o',0);
        }
        return 'success';
    }

}