<?php
/**
 * 充值管理
 * Class Recharge
 * @package app\index\controller
 */

namespace app\index\controller;

use think\facade\Config;
use think\facade\Request;
use think\facade\Session;
use \app\index\model\Recharge as Recharge_m;

class Recharge extends Base
{
    /**
     * 充值通道映射：前端 type → Model 方法名
     * 格式: '前端type' => ['model_method' => '方法名', 'has_param3' => false]
     */
    private static $payChannels = [
        'shineupay'     => ['method' => 'shineupay', 'has_param3' => false],
        'Oceanpay'      => ['method' => 'oceanpay', 'has_param3' => false],
        'Global_pay'    => ['method' => 'Global_pay', 'has_param3' => false],
        'pay51'         => ['method' => 'pay51', 'has_param3' => false],
        'Dzxum'         => ['method' => 'dzxum', 'has_param3' => false],
        'Dzxum2'        => ['method' => 'dzxum2', 'has_param3' => false],
        'FastPay'       => ['method' => 'FastPay', 'has_param3' => false],
        'sunpay'        => ['method' => 'sunpay', 'has_param3' => false],
        'htpay'         => ['method' => 'htpay', 'has_param3' => false],
        'fastpay2'      => ['method' => 'fastpay2', 'has_param3' => false],
        'lepay'         => ['method' => 'lepay', 'has_param3' => false],
        'wakapay'       => ['method' => 'wakapay', 'has_param3' => false],
        'mpay'          => ['method' => 'mpay', 'has_param3' => false],
        'inrpay'        => ['method' => 'inrpay', 'has_param3' => false],
        'xianxia'       => ['method' => 'xianxia', 'has_param3' => false],
        'xianxia2'      => ['method' => 'xianxia2', 'has_param3' => false],
        'xianxia3'      => ['method' => 'xianxia3', 'has_param3' => false],
        'xianxia4'      => ['method' => 'xianxia4', 'has_param3' => false],
        // ONEPAY 子通道（Model onepay() 方法需要第三个参数 $pay_type）
        'onepay_easypaisa'  => ['method' => 'onepay', 'has_param3' => true],
        'onepay_bankcard'   => ['method' => 'onepay', 'has_param3' => true],
        'onepay_jazzcash'   => ['method' => 'onepay', 'has_param3' => true],
    ];

    public function index()
    {
        $recharge_api = Recharge_m::recharge_api();
        $this->assign('recharge_api', $recharge_api);
        return $this->fetch();
    }

    public function trc20()
    {
        $money = input('money');
        $user_id = Session::get('user_id');
        $user_info = \app\ch\model\Base::get_user_info($user_id);
        $this->assign('data', ['id' => $user_info['id'], 'money' => $money]);
        return $this->fetch();
    }

    public function erc20()
    {
        $money = input('money');
        $user_id = Session::get('user_id');
        $user_info = \app\ch\model\Base::get_user_info($user_id);
        $this->assign('data', ['id' => $user_info['id'], 'money' => $money]);
        return $this->fetch();
    }

    /**
     * 线下充值
     */
    public function offline()
    {
        $key = $_SESSION['offline'] ?? '';
        $suffix = empty($key) ? '' : '_' . $key;
        $suffix2 = empty($key) ? '' : $key;

        $prefix = 'offline_bank_name';
        $configs = [
            'offline_bank_name'  => \app\index\model\Base::get_config($prefix . $suffix),
            'offline_bank_number' => \app\index\model\Base::get_config('offline_bank_number' . $suffix),
            'offline_bank_user'  => \app\index\model\Base::get_config('offline_bank_user' . $suffix),
            'offline_bank_name_kai' => \app\index\model\Base::get_config('offline_bank_name_kai' . $suffix2),
        ];
        $this->assign($configs);

        $this->assign('recharge_number', $_SESSION['xianxia_recharge'] ?? '');
        $this->assign('order_id', $_SESSION['order_id'] ?? '');
        return $this->fetch();
    }

    /**
     * 在线充值入口
     */
    public function recharge()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        $start_recharge = \app\index\model\Base::get_config('start_recharge');

        if (!Request::isPost()) return;

        $money = Request::post('money');
        if ($money < $start_recharge) {
            return ['code' => 0, 'msg' => lang("MinimumRecharge") . $start_recharge];
        }

        $type = Request::post('type');

        // Razorpay 暂时关闭
        if ($type === 'Razorpay') {
            return ['code' => 0, 'msg' => lang("NotOpened")];
        }

        // USDT 返回页面
        if ($type === 'usdt') {
            return $this->fetch('usdt');
        }

        // 查找通道配置
        if (!isset(self::$payChannels[$type])) {
            return ['code' => 0, 'msg' => 'Unknown payment channel'];
        }

        $channel = self::$payChannels[$type];
        $method = $channel['method'];

        if ($channel['has_param3']) {
            // ONEPAY 子通道：type=onepay_easypaisa → pay_type=easypaisa
            $pay_type = strpos($type, 'onepay_') === 0 ? substr($type, 7) : (Request::post('param3') ?? '');
            $res = Recharge_m::$method($user_info, $money, $pay_type);
        } else {
            $res = Recharge_m::$method($user_info, $money);
        }

        // 特殊通道返回格式处理
        if ($type === 'shineupay' && $res !== 'error') {
            return ['code' => 1, 'data' => $res];
        }
        if ($type === 'Oceanpay' && is_array($res) && $res['code'] === 1) {
            return ['code' => 1, 'data' => $res['data']];
        }
        if (strpos($type, 'onepay_') === 0 && is_array($res) && $res['code'] === 1) {
            return ['code' => 1, 'data' => $res['data']];
        }

        return $res;
    }

    /**
     * USDT 汇率换算
     */
    public function usdt()
    {
        $recharge = input('a', '1000');
        $rate_temp = \app\index\model\Base::get_config('usdt_huilv');
        $rate = !empty($rate_temp) ? $rate_temp : "1";

        $usdt_number = floatval($recharge) / floatval($rate);
        $usdt = strval(number_format($usdt_number, 2));

        $this->assign('recharge', $recharge);
        $this->assign('rate', $rate);
        $this->assign('usdt', $usdt);

        $info = Recharge_m::contact_cs();
        $this->assign('telegram', $info['wechat']);

        return $this->fetch();
    }
}
