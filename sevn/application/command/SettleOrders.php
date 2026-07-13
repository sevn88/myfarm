<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\facade\Log;

class SettleOrders extends Command
{
    protected function configure()
    {
        $this->setName('order:settle')
            ->setDescription('批量结算所有已到期的订单');
    }

    protected function execute(Input $input, Output $output)
    {
        $now = time();
        $startTime = microtime(true);

        $output->writeln('[信息] 开始批量订单结算: ' . date('Y-m-d H:i:s'));

        // 查找所有待结算的订单：状态=2 且 settle_time <= 当前时间
        $dueOrders = Db::name('fd_order')
            ->where('status', 2)
            ->where('settle_time', '<=', $now)
            ->order('id', 'asc')
            ->select();

        if (empty($dueOrders)) {
            $output->writeln('[信息] 没有需要结算的订单');
            return 0;
        }

        $output->writeln("[信息] 找到 " . count($dueOrders) . " 个待结算订单");

        $successCount = 0;
        $skipCount = 0;
        $failCount = 0;
        $exceptions = [];

        foreach ($dueOrders as $order) {
            try {
                // 检查用户是否有待处理订单（状态0或3）
                $pendingCount = Db::name('fd_order')
                    ->where('uid', $order['uid'])
                    ->where('status', 'in', [0, 3])
                    ->count();

                if ($pendingCount > 0) {
                    $output->writeln("  [跳过] 订单 {$order['id']} (用户{$order['uid']}) - 用户有待处理订单");
                    $skipCount++;
                    continue;
                }

                // 调用结算方法（带事务保护）
                $result = self::settlement($order['id']);
                if ($result === 'success') {
                    $successCount++;
                    $output->writeln("  [完成] 订单 {$order['id']} (用户{$order['uid']}) 结算成功");
                } else {
                    $failCount++;
                    $output->writeln("  [失败] 订单 {$order['id']} (用户{$order['uid']}) 结算失败");
                }
            } catch (\Exception $e) {
                $failCount++;
                $errorMsg = "订单 {$order['id']} (用户{$order['uid']}): " . $e->getMessage();
                $output->writeln("  [错误] {$errorMsg}");
                $exceptions[] = $errorMsg;

                // 记录日志
                Log::error('订单结算异常', [
                    'order_id' => $order['id'],
                    'uid' => $order['uid'],
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $elapsed = round(microtime(true) - $startTime, 3);

        $output->writeln("[信息] 结算完成: 成功={$successCount}, 跳过={$skipCount}, 失败={$failCount}, 耗时={$elapsed}秒");

        if ($failCount > 0) {
            $output->writeln("[警告] 有 {$failCount} 个订单结算失败，请检查日志");
        }

        // 记录汇总日志
        Log::info('批量结算汇总', [
            '总数' => count($dueOrders),
            '成功' => $successCount,
            '跳过' => $skipCount,
            '失败' => $failCount,
            '耗时秒' => $elapsed,
        ]);

        return $failCount > 0 ? 1 : 0;
    }

    /**
     * 订单结算方法（带事务保护）
     */
    private static function settlement($id)
    {
        try {
            $result = Db::transaction(function () use ($id) {
                $order_info = Db::name('fd_order')->where('id', $id)->find();
                if (!$order_info) {
                    throw new \Exception('订单不存在');
                }

                $balance = intval($order_info['goods_price']) + intval($order_info['order_earnings']);

                // 更新订单状态为已完成
                $orderUpdated = Db::name('fd_order')
                    ->where('id', $order_info['id'])
                    ->update(['status' => 1, 'update_time' => time()]);
                if (!$orderUpdated) {
                    throw new \Exception('更新订单状态失败');
                }

                // 原子性更新用户账户：减少冻结、增加余额和总收入
                $userResult = Db::name('fd_user')
                    ->where('id', $order_info['uid'])
                    ->where('freeze', '>=', $order_info['goods_price'])
                    ->update([
                        'freeze'  => Db::raw('freeze - ' . intval($order_info['goods_price'])),
                        'balance' => Db::raw('balance + ' . $balance),
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
}
