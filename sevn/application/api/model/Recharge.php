<?php

namespace app\api\model;

use think\Model;
use think\Db;

/**
 * 充值回调处理
 * 所有支付通道的回调逻辑相同：查订单 → 验金额 → 加余额 → 更新状态
 * 通过静态方法 dispatch() 统一分发
 */
class Recharge extends Model
{
    /**
     * 统一充值回调处理
     *
     * @param string $channel 渠道标识（用于日志记录，可选）
     * @param array $info 回调参数，必须包含 order_id、money、money_real、status
     * @return string SUCCESS / ERROR
     */
    public static function handleCallback(string $channel = '', array $info): string
    {
        $list = Db::name('fd_recharge')->where('order_id', $info['order_id'])->select();

        if (!$list) {
            return 'ERROR';
        }

        // 金额校验
        if (isset($info['money_real']) && isset($list[0]['money'])) {
            if (floatval($list[0]['money']) != floatval($info['money_real'])) {
                return 'ERROR';
            }
        }

        // 支付成功 → 增加用户余额
        if (intval($info['status']) === 1 && intval($list[0]['status']) !== 1) {
            Db::name('fd_user')
                ->where('id', intval($list[0]['uid']))
                ->setInc('balance', floatval($info['money_real']));
        }

        // 更新订单状态
        $re = Db::name('fd_recharge')
            ->where('order_id', $info['order_id'])
            ->update($info);

        return $re !== false ? 'SUCCESS' : 'ERROR';
    }

    // ==================== 以下为兼容旧代码的代理方法 ====================
    // 每个通道调用统一的 handleCallback，保持原有方法签名不变

    public function pay51($info)
    {
        return self::handleCallback('pay51', $info);
    }

    public function dzxum($info)
    {
        return self::handleCallback('dzxum', $info);
    }

    public function fastpay($info)
    {
        return self::handleCallback('fastpay', $info);
    }

    public function fastpay2($info)
    {
        return self::handleCallback('fastpay2', $info);
    }

    public function sunpay($info)
    {
        return self::handleCallback('sunpay', $info);
    }

    public function lepay($info)
    {
        return self::handleCallback('lepay', $info);
    }

    public function htpay($info)
    {
        return self::handleCallback('htpay', $info);
    }

    public function onepay($info)
    {
        return self::handleCallback('onepay', $info);
    }

    public function inrpay($info)
    {
        return self::handleCallback('inrpay', $info);
    }
}
