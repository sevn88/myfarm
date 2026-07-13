<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

// 加载外部配置（优先从 .env 环境变量读取）
$sms_config = include __DIR__ . '/sms.php';
$pay_config = include __DIR__ . '/payments.php';
$common_config = include __DIR__ . '/common.php';

return array_merge([

    // 应用目录
    'app_namespace'     => 'app',
    // 应用调试模式
    'app_debug'         => true,
    // 应用Trace
    'app_trace'         => false,

    // 默认模块
    'default_module'    => 'index',
    // 默认跳转页面
    'dispatch_success_tmpl'  => __DIR__ . '/../common/jump.tpl',
    'dispatch_error_tmpl'    => __DIR__ . '/../common/jump.tpl',

], [
    // 从外部配置加载
    'sms_code_account' => $sms_config['sms_code_account'],
    'sms_code_key'     => $sms_config['sms_code_key'],
    'sms_code_zone'    => $sms_config['sms_code_zone'],

    'commission' => $common_config['commission'],

    // 支付通道配置
    'shineupay_id'   => $pay_config['shineupay_id'],
    'shineupay_key'  => $pay_config['shineupay_key'],
    'shineupay_url'  => $pay_config['shineupay_url'],
    'oceanpay_url'   => $pay_config['oceanpay_url'],
    'oceanpay_code'  => $pay_config['oceanpay_code'],
    'oceanpay_key'   => $pay_config['oceanpay_key'],
    'oceanpay_paycode' => $pay_config['oceanpay_paycode'],
    'dzxum_url'           => $pay_config['dzxum_url'],
    'dzxum_key'           => $pay_config['dzxum_key'],
    'dzxum_mch_id'        => $pay_config['dzxum_mch_id'],
    'dzxum_notify_url'    => $pay_config['dzxum_notify_url'],
    'dzxum_return_url'    => $pay_config['dzxum_return_url'],
    'dzxum_pay_type'      => $pay_config['dzxum_pay_type'],
    'dzxum_bank_code'     => $pay_config['dzxum_bank_code'],
    'dzxum_pay_type2'     => $pay_config['dzxum_pay_type2'],
    'dzxum_bank_code2'    => $pay_config['dzxum_bank_code2'],
    'fastpay_url'          => $pay_config['fastpay_url'],
    'fastpay_mch_id'       => $pay_config['fastpay_mch_id'],
    'fastpay_key'          => $pay_config['fastpay_key'],
    'fastpay_notify_url'   => $pay_config['fastpay_notify_url'],
    'fastpay_return_url'   => $pay_config['fastpay_return_url'],
    'fastpay_type'         => $pay_config['fastpay_type'],
    'fastpay_version'      => $pay_config['fastpay_version'],
    'sunpay_url'          => $pay_config['sunpay_url'],
    'sunpay_key'          => $pay_config['sunpay_key'],
    'sunpay_mch_id'       => $pay_config['sunpay_mch_id'],
    'sunpay_notify_url'   => $pay_config['sunpay_notify_url'],
    'sunpay_return_url'   => $pay_config['sunpay_return_url'],
    'sunpay_pay_type'     => $pay_config['sunpay_pay_type'],
    'sunpay_bank_code'    => $pay_config['sunpay_bank_code'],
    'htpay_url'          => $pay_config['htpay_url'],
    'htpay_key'          => $pay_config['htpay_key'],
    'htpay_mch_id'       => $pay_config['htpay_mch_id'],
    'htpay_notify_url'   => $pay_config['htpay_notify_url'],
    'htpay_return_url'   => $pay_config['htpay_return_url'],
    'htpay_pay_type'     => $pay_config['htpay_pay_type'],
    'htpay_bank_code'    => $pay_config['htpay_bank_code'],
    'fastpay2_url'          => $pay_config['fastpay2_url'],
    'fastpay2_mch_id'       => $pay_config['fastpay2_mch_id'],
    'fastpay2_key'          => $pay_config['fastpay2_key'],
    'fastpay2_notify_url'   => $pay_config['fastpay2_notify_url'],
    'fastpay2_return_url'   => $pay_config['fastpay2_return_url'],
    'fastpay2_type'         => $pay_config['fastpay2_type'],
    'fastpay2_version'      => $pay_config['fastpay2_version'],
    'lepay_url'          => $pay_config['lepay_url'],
    'lepay_key'          => $pay_config['lepay_key'],
    'lepay_mch_id'       => $pay_config['lepay_mch_id'],
    'lepay_notify_url'   => $pay_config['lepay_notify_url'],
    'lepay_return_url'   => $pay_config['lepay_return_url'],
    'lepay_pay_type'     => $pay_config['lepay_pay_type'],
    'lepay_bank_code'    => $pay_config['lepay_bank_code'],
    'macpay_url'               => $pay_config['macpay_url'],
    'macpay_mch_public_key'    => $pay_config['macpay_mch_public_key'],
    'macpay_customer_public_key' => $pay_config['macpay_customer_public_key'],
    'macpay_customer_private_key' => $pay_config['macpay_customer_private_key'],
    'macpay_mch_id'            => $pay_config['macpay_mch_id'],
    'macpay_notify_url'        => $pay_config['macpay_notify_url'],
    'macpay_return_url'        => $pay_config['macpay_return_url'],
    'wakapay_url'          => $pay_config['wakapay_url'],
    'wakapay_key'          => $pay_config['wakapay_key'],
    'wakapay_mch_id'       => $pay_config['wakapay_mch_id'],
    'wakapay_notify_url'   => $pay_config['wakapay_notify_url'],
    'wakapay_return_url'   => $pay_config['wakapay_return_url'],
    'wakapay_pay_type'     => $pay_config['wakapay_pay_type'],
    'wakapay_bank_code'    => $pay_config['wakapay_bank_code'],
    'onepay_url'          => $pay_config['onepay_url'],
    'onepay_key'          => $pay_config['onepay_key'],
    'onepay_mch_id'       => $pay_config['onepay_mch_id'],
    'onepay_aes'          => $pay_config['onepay_aes'],
    'onepay_notify_url'   => $pay_config['onepay_notify_url'],
    'onepay_return_url'   => $pay_config['onepay_return_url'],
    'onepay_pay_type'     => $pay_config['onepay_pay_type'],
    'onepay_bank_code'    => $pay_config['onepay_bank_code'],
    'onepay_pay_code'     => $pay_config['onepay_pay_code'],
    'mpay_url'          => $pay_config['mpay_url'],
    'mpay_key'          => $pay_config['mpay_key'],
    'mpay_mch_id'       => $pay_config['mpay_mch_id'],
    'mpay_notify_url'   => $pay_config['mpay_notify_url'],
    'mpay_return_url'   => $pay_config['mpay_return_url'],
    'mpay_pay_type'     => $pay_config['mpay_pay_type'],
    'mpay_bank_code'    => $pay_config['mpay_bank_code'],
    'mpay_bank_type'    => $pay_config['mpay_bank_type'],
    'mpay_pay_code'     => $pay_config['mpay_pay_code'],
    'inrpay_url'          => $pay_config['inrpay_url'],
    'inrpay_key'          => $pay_config['inrpay_key'],
    'inrpay_mch_id'       => $pay_config['inrpay_mch_id'],
    'inrpay_notify_url'   => $pay_config['inrpay_notify_url'],
    'inrpay_return_url'   => $pay_config['inrpay_return_url'],
    'inrpay_pay_type'     => $pay_config['inrpay_pay_type'],
    'inrpay_bank_code'    => $pay_config['inrpay_bank_code'],
    'inrpay_bank_type'    => $pay_config['inrpay_bank_type'],
    'inrpay_pay_code'     => $pay_config['inrpay_pay_code'],
]);
