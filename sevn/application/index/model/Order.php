<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 4:29
 * QQ:1467572213
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Order extends Model
{
    public static function index($user_id, $type)
    {
        
        if ($type == null) $type = 0;
        switch ($type) {
            case 0:
                $where['uid'] = $user_id;
                $where['status'] = $type;
                $order_freezon_list = Db::name('fd_order')->where('uid',$user_id)->where('status','3')->count();
                if ($order_freezon_list>0){
                    $where['id']=0;
                }
                $order_list = Db::name('fd_order')->where($where)->order('id', 'asc')
                    ->paginate(1, false, [
                        'query' => request()->param(),
                        'type' => 'page\page',
                        'var_page' => 'page',
                    ]);
//                echo Db::getLastSql();
                return $order_list;
                break;
            case 1:
            case 2:
            $where['uid'] = $user_id;
            $where['status'] = $type;
            $order_list = Db::name('fd_order')->where($where)->order('id', 'asc')
                ->paginate(10, false, [
                    'query' => request()->param(),
                    'type' => 'page\page',
                    'var_page' => 'page',
                ]);
            return $order_list;
            break;
            case 3:
                $where['uid'] = $user_id;
                $where['status'] = $type;
                $order_list = Db::name('fd_order')->where($where)->order('id', 'asc')
                    ->paginate(1, false, [
                        'query' => request()->param(),
                        'type' => 'page\page',
                        'var_page' => 'page',
                    ]);
                return $order_list;
                break;
        }

    }

    public static function order_finish($id, $user_info)
    {
        $where['id'] = $id;
        $where['uid'] = $user_info['id'];
        $order_info = Db::name('fd_order')->where($where)->find();
        if (!$order_info) {
            return ['status' => 'error'];
        }

        // 实时读取最新余额，防止过期快照导致的并发问题
        $fresh_user = Db::name('fd_user')->where('id', $user_info['id'])->find();
        if (!$fresh_user || $fresh_user['balance'] < $order_info['goods_price']) {
            return ['status' => 'error_money', 'less_money' => $order_info['goods_price'] - ($fresh_user['balance'] ?? 0)];
        }

        if ($order_info['status'] == 0) {
            $dec_money = self::dec_money($fresh_user, $order_info['goods_price']);
            if ($dec_money == 'success') {
                // 付款成功，立即结算（不调用定时任务，直接完成订单）
                $result = self::instant_settlement($id, $fresh_user['id']);
                if ($result === 'success') {
                    $map['uid'] = $fresh_user['id'];
                    $map['status'] = 0;
                    $check_order = Db::name('fd_order')->where($map)->order('id', 'asc')->find();
                    if ($check_order) {
                        return ['status' => 'success_go', 'data' => $check_order];
                    } else {
                        return ['status' => 'success'];
                    }
                } else {
                    return ['status' => 'error'];
                }
            } else {
                return ['status' => 'error_dec'];
            }
        } else {
            return ['status' => 'error_status'];
        }
    }

    /**
     * 即时结算：付款成功后立即完成订单，不再等待定时任务
     */
    private static function instant_settlement($order_id, $user_id)
    {
        try {
            $result = Db::transaction(function () use ($order_id, $user_id) {
                // 1. 查找订单
                $order_info = Db::name('fd_order')->where('id', $order_id)->find();
                if (!$order_info || $order_info['uid'] != $user_id) {
                    throw new \Exception('订单不存在');
                }

                $balance = intval($order_info['goods_price']) + intval($order_info['order_earnings']);

                // 2. 更新订单状态为已完成（status=1）
                $orderUpdated = Db::name('fd_order')
                    ->where('id', $order_id)
                    ->update(['status' => 1, 'update_time' => time()]);
                if (!$orderUpdated) {
                    throw new \Exception('更新订单状态失败');
                }

                // 3. 原子性更新用户账户：减少冻结、增加余额和总收入
                $userResult = Db::name('fd_user')
                    ->where('id', $user_id)
                    ->where('freeze', '>=', $order_info['goods_price'])
                    ->update([
                        'freeze'  => Db::raw('freeze - ' . intval($order_info['goods_price'])),
                        'balance' => Db::raw('balance + ' . $balance),
                        'total'   => Db::raw('total + ' . intval($order_info['order_earnings'])),
                    ]);
                if (!$userResult) {
                    throw new \Exception('用户余额更新失败');
                }

                // 4. 检查用户是否还有其他待结算订单（status=2）
                $remaining = Db::name('fd_order')
                    ->where('uid', $user_id)
                    ->where('status', 2)
                    ->count();

                if ($remaining == 0) {
                    // 所有订单已结算，更新统计计数器
                    Db::name('fd_user')
                        ->where('id', $user_id)
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

    public static function dec_money($user_info, $money)
    {
        try {
            Db::startTrans();

            // 使用原子更新：在一个 SQL 语句中同时减少余额、增加冻结
            // WHERE balance >= money 防止余额不足时扣款
            $affected = Db::name('fd_user')
                ->where('id', $user_info['id'])
                ->where('balance', '>=', $money)
                ->where('status', 1)
                ->dec('balance', $money)
                ->inc('freeze', $money)
                ->update([]);

            if ($affected === false || $affected == 0) {
                Db::rollback();
                return 'error';  // 余额不足或用户已禁用
            }

            Db::commit();
            return 'success';
        } catch (\Exception $e) {
            Db::rollback();
            return 'error';
        }
    }

    public static function info($user_id, $id)
    {
        $where['id'] = $id;
        $where['uid'] = $user_id;
        $info = Db::name('fd_order')->where($where)->find();
        return $info;
    }

    public static function check_timeout($order_id)
    {
        $now_time = time();
        $where = [
            ['id', '=', $order_id],
            ['status', '=', 0],
            ['sub_time', '<', $now_time],
        ];
        $check = Db::name('fd_order')->where($where)->find();
        if ($check) {
            Db::name('fd_order')->where($where)->setField('status', 3);
            return ['status'=>1,'data'=>$check];
        } else {
            return ['status'=>0];
        }
    }

    public static function check_settlement($order_id)
    {
        $where = [
            ['id', '=', $order_id],
            ['status', '=', 1],
        ];
        $check = Db::name('fd_order')->where($where)->find();
        if ($check) {
            return ['status'=>1,'data'=>$check];
        } else {
            return ['status'=>0];
        }
    }

}