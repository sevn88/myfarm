<?php
// 充值接口配置
// ⚠️ 重要: 生产环境请使用环境变量覆盖以下值
return [
    // shineupay
    'shineupay_id'   => getenv('SHINEUPAY_ID') ?: 'AV5211YK9OG03750',
    'shineupay_key'  => getenv('SHINEUPAY_KEY') ?: 'b149d5d04b54404087881b7d832fb762',
    'shineupay_url'  => getenv('SHINEUPAY_URL') ?: 'https://testgateway.shineupay.com/pay/create',

    // oceanpay
    'oceanpay_url'   => getenv('OCEANPAY_URL') ?: 'https://www.foxconny.com/api/outer/collections/addOrderByLndia',
    'oceanpay_code'  => getenv('OCEANPAY_CODE') ?: '11195',
    'oceanpay_key'   => getenv('OCEANPAY_KEY') ?: '42cwve5Zd6tStbrA6GEIkq2201AylqNf',
    'oceanpay_paycode' => getenv('OCEANPAY_PAYCODE') ?: '909',

    // dzxum
    'dzxum_url'           => getenv('DZXUM_URL') ?: 'https://payment.dzxum.com/pay/web',
    'dzxum_key'           => getenv('DZXUM_KEY') ?: '775883f2f6fd4e5bb0e20404becd9d37',
    'dzxum_mch_id'        => getenv('DZXUM_MCH_ID') ?: '100009075',
    'dzxum_notify_url'    => getenv('DZXUM_NOTIFY_URL') ?: 'https://www.amznol.com/api/dzxum',
    'dzxum_return_url'    => getenv('DZXUM_RETURN_URL') ?: 'https://www.amznol.com/',
    'dzxum_pay_type'      => getenv('DZXUM_PAY_TYPE') ?: 'UPI原生一类',
    'dzxum_bank_code'     => getenv('DZXUM_BANK_CODE') ?: '102',

    // dzxum2
    'dzxum_pay_type2' => getenv('DZXUM_PAY_TYPE2') ?: 'UPI跑分二类',
    'dzxum_bank_code2' => getenv('DZXUM_BANK_CODE2') ?: '122',

    // fastpay
    'fastpay_url'          => getenv('FASTPAY_URL') ?: 'https://api.fast8866.com/okex-admin/okex/api/v2/pay',
    'fastpay_mch_id'       => getenv('FASTPAY_MCH_ID') ?: '1608405137909088257',
    'fastpay_key'          => getenv('FASTPAY_KEY') ?: '5798d17a40458e54d865b2f6a25e3033',
    'fastpay_notify_url'   => getenv('FASTPAY_NOTIFY_URL') ?: 'https://www.amznol.com/api/fastpay',
    'fastpay_return_url'   => getenv('FASTPAY_RETURN_URL') ?: 'https://www.amznol.com/',
    'fastpay_type'         => getenv('FASTPAY_TYPE') ?: 8,
    'fastpay_version'      => getenv('FASTPAY_VERSION') ?: '2.0.2',

    // sunpay
    'sunpay_url'          => getenv('SUNPAY_URL') ?: 'https://pay.sunpayonline.xyz/pay/web',
    'sunpay_key'          => getenv('SUNPAY_KEY') ?: 'd522877bc23244b48f001a531b7f22e0',
    'sunpay_mch_id'       => getenv('SUNPAY_MCH_ID') ?: '770555019',
    'sunpay_notify_url'   => getenv('SUNPAY_NOTIFY_URL') ?: 'https://www.amznol.com/api/sunpay',
    'sunpay_return_url'   => getenv('SUNPAY_RETURN_URL') ?: 'https://www.amznol.com/',
    'sunpay_pay_type'     => getenv('SUNPAY_PAY_TYPE') ?: 'UPI原生一类',
    'sunpay_bank_code'    => getenv('SUNPAY_BANK_CODE') ?: '2202',

    // htpay
    'htpay_url'          => getenv('HTPAY_URL') ?: 'https://pay.xlpay888.com/payment/collection',
    'htpay_key'          => getenv('HTPAY_KEY') ?: '8tuQuahoSwfhRLmEQS2X',
    'htpay_mch_id'       => getenv('HTPAY_MCH_ID') ?: 'xl006',
    'htpay_notify_url'   => getenv('HTPAY_NOTIFY_URL') ?: 'https://www.smsxnbd.top/api/htpay',
    'htpay_return_url'   => getenv('HTPAY_RETURN_URL') ?: 'https://www.smsxnbd.top/',
    'htpay_pay_type'     => getenv('HTPAY_PAY_TYPE') ?: 'UPI原生一类',
    'htpay_bank_code'    => getenv('HTPAY_BANK_CODE') ?: '2202',

    // fastpay2
    'fastpay2_url'          => getenv('FASTPAY2_URL') ?: 'http://www.fast-pay.cc/gateway.aspx',
    'fastpay2_mch_id'       => getenv('FASTPAY2_MCH_ID') ?: '1002729',
    'fastpay2_key'          => getenv('FASTPAY2_KEY') ?: '6bd1737d8d072e72adcc99ccf6c10c25',
    'fastpay2_notify_url'   => getenv('FASTPAY2_NOTIFY_URL') ?: 'https://www.amznol.com/api/fastpay3',
    'fastpay2_return_url'   => getenv('FASTPAY2_RETURN_URL') ?: 'https://www.amznol.com/',
    'fastpay2_type'         => getenv('FASTPAY2_TYPE') ?: 8,
    'fastpay2_version'      => getenv('FASTPAY2_VERSION') ?: '2.0.2',

    // lepay
    'lepay_url'          => getenv('LEPAY_URL') ?: 'https://payment.lexmpay.com/pay/web',
    'lepay_key'          => getenv('LEPAY_KEY') ?: '9TETFRTQYGQ9PEJABQXBF7HGEE1HCBLD',
    'lepay_mch_id'       => getenv('LEPAY_MCH_ID') ?: '991917010',
    'lepay_notify_url'   => getenv('LEPAY_NOTIFY_URL') ?: 'https://www.amznol.com/api/lepay',
    'lepay_return_url'   => getenv('LEPAY_RETURN_URL') ?: 'https://www.amznol.com/',
    'lepay_pay_type'     => getenv('LEPAY_PAY_TYPE') ?: 'UPI原生一类',
    'lepay_bank_code'    => getenv('LEPAY_BANK_CODE') ?: '2220',

    // macpay
    'macpay_url'               => getenv('MACPAY_URL') ?: 'https://api.macpayss.com/gateway/deposit/create',
    'macpay_mch_public_key'    => getenv('MACPAY_MCH_PUBLIC_KEY') ?: '',
    'macpay_customer_public_key' => getenv('MACPAY_CUSTOMER_PUBLIC_KEY') ?: '',
    'macpay_customer_private_key' => getenv('MACPAY_PRIVATE_KEY') ?: '',
    'macpay_mch_id'            => getenv('MACPAY_MCH_ID') ?: '10010001',
    'macpay_notify_url'        => getenv('MACPAY_NOTIFY_URL') ?: 'https://www.amznol.com/api/macpay',
    'macpay_return_url'        => getenv('MACPAY_RETURN_URL') ?: 'https://www.amznol.com/',

    // wakapay
    'wakapay_url'          => getenv('WAKAPAY_URL') ?: 'https://waka-pay.com/gateway/',
    'wakapay_key'          => getenv('WAKAPAY_KEY') ?: 'fe6e43443b438ac1f9897c74633b32c4',
    'wakapay_mch_id'       => getenv('WAKAPAY_MCH_ID') ?: '861130',
    'wakapay_notify_url'   => getenv('WAKAPAY_NOTIFY_URL') ?: 'https://www.amznol.com/api/wakapay',
    'wakapay_return_url'   => getenv('WAKAPAY_RETURN_URL') ?: 'https://www.amznol.com/',
    'wakapay_pay_type'     => getenv('WAKAPAY_PAY_TYPE') ?: 'UPI原生一类',
    'wakapay_bank_code'    => getenv('WAKAPAY_BANK_CODE') ?: '30000',

    // onepay
    'onepay_url'          => getenv('ONEPAY_URL') ?: 'https://api-bdt.onepay.news/api/v1/order/receive',
    'onepay_key'          => getenv('ONEPAY_KEY') ?: '18nw3I45ya',
    'onepay_mch_id'       => getenv('ONEPAY_MCH_ID') ?: 'amznol',
    'onepay_aes'          => getenv('ONEPAY_AES') ?: 'yE5ix5Zz3U2gI27G',
    'onepay_notify_url'   => getenv('ONEPAY_NOTIFY_URL') ?: 'https://www.smsxnbd.top/api/onepay',
    'onepay_return_url'   => getenv('ONEPAY_RETURN_URL') ?: 'https://www.smsxnbd.top/',
    'onepay_pay_type'     => getenv('ONEPAY_PAY_TYPE') ?: '2',
    'onepay_bank_code'    => getenv('ONEPAY_BANK_CODE') ?: 'B9081',
    'onepay_pay_code'     => getenv('ONEPAY_PAY_CODE') ?: 'B9081',

    // mpay
    'mpay_url'          => getenv('MPAY_URL') ?: 'https://api.mpayin.com/admin/platform/api/out/pay',
    'mpay_key'          => getenv('MPAY_KEY') ?: 'be801e40042931b9d33fe1a89875cdcc',
    'mpay_mch_id'       => getenv('MPAY_MCH_ID') ?: '1401099966654808066',
    'mpay_notify_url'   => getenv('MPAY_NOTIFY_URL') ?: 'https://www.amdb666.com/api/mpay',
    'mpay_return_url'   => getenv('MPAY_RETURN_URL') ?: 'https://www.amdb666.com/',
    'mpay_pay_type'     => getenv('MPAY_PAY_TYPE') ?: 'UPI原生一类',
    'mpay_bank_code'    => getenv('MPAY_BANK_CODE') ?: '10005',
    'mpay_bank_type'    => getenv('MPAY_BANK_TYPE') ?: 'BANK',
    'mpay_pay_code'     => getenv('MPAY_PAY_CODE') ?: 'B9081',

    // inrpay
    'inrpay_url'          => getenv('INRPAY_URL') ?: 'https://inrpay.shop/Pay_Index.html',
    'inrpay_key'          => getenv('INRPAY_KEY') ?: 'wvof6hvglwee9qznkbl4qxza4cp06hik',
    'inrpay_mch_id'       => getenv('INRPAY_MCH_ID') ?: '240145422',
    'inrpay_notify_url'   => getenv('INRPAY_NOTIFY_URL') ?: 'https://www.amdb666.com/api/inrpay',
    'inrpay_return_url'   => getenv('INRPAY_RETURN_URL') ?: 'https://www.amdb666.com/',
    'inrpay_pay_type'     => getenv('INRPAY_PAY_TYPE') ?: 'UPI原生一类',
    'inrpay_bank_code'    => getenv('INRPAY_BANK_CODE') ?: '10005',
    'inrpay_bank_type'    => getenv('INRPAY_BANK_TYPE') ?: 'BANK',
    'inrpay_pay_code'     => getenv('INRPAY_PAY_CODE') ?: '1',
];
