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

return [
    //提现接口Oceanpay
    'oceanpay_w_url'=>'https://www.foxconny.com/api/outer/merwithdraw/addPaid',
    'oceanpay_w_code'=>'11195',
    'oceanpay_w_key'=>'42cwve5Zd6tStbrA6GEIkq2201AylqNf',
    'oceanpay_w_paycode'=>'909',

    //充值接口 51pay -  代付

    'pay51_daifu_url'=>'https://api.eggoout.com/payout/unifiedorder.do',
    'pay51_merchantNo'=>"M221206145930002337",
    'pay51_channelCode'=>'YDPAYPLUSZJBDF',
    'pay51_daifu_huitiao_url'=>'https://www.amznol.com/api/re512',
    'pay51_key'=>'c7aa02e69235484faeb4b22a186c1aa7',

    ////充值接口 dzxum -  代付
    'dzxum_daifu_url'=>'https://payment.dzxum.com/pay/transfer', //接口地址
    'dzxum_key'=>'3CEAOZCB3ASSFI9KUFYFY5JGJK0SVGOC',
    'dzxum_mch_id'=>'100009075',//商户编号
    'dzxum_notify_url'=>'https://www.amznol.com/api/dzxum2',//回调地址
    'dzxum_return_url'=>'https://www.amznol.com/',
    'dzxum_pay_type'=>'UPI原生一类',//支付类型
    'dzxum_bank_code'=>'122',//通道编码
    
    
    //充值接口FastPay
    'fastpay_daifu_url'=>'https://api.fast8866.com/okex-admin/okex/api/v2/df',
    'fastpay_mch_id'=>'1608405137909088257',
    'fastpay_key'=>'5798d17a40458e54d865b2f6a25e3033',
    'fastpay_notify_url'=>'https://www.amznol.com/api/fastpay2',//回调地址
    'fastpay_return_url'=>'https://www.amznol.com/',
    'fastpay_type'=>1,//支付类型。1：银行卡 8：UPI
    'fastpay_version'=>'2.0.2',


    ////代付接口 sunpay -  代付
    'sunpay_daifu_url'=>'https://pay.sunpayonline.xyz/pay/transfer', //接口地址
    'sunpay_key'=>'2NDCOPHZGYPQFIVXGMD7YRMGWVMDIK5D', //正式
    'sunpay_mch_id'=>'770555019',//商户编号 正式
    // 'sunpay_key'=>'d76276295d6f43c49d9e450327af4d2f', //测试
    // 'sunpay_mch_id'=>'770111001',//商户编号 测试
    'sunpay_notify_url'=>'https://www.amznol.com/api/sunpay2',//回调地址
    'sunpay_return_url'=>'https://www.amznol.com/',
    'sunpay_pay_type'=>'UPI原生一类',//支付类型
    'sunpay_bank_code'=>'2202',//通道编码
    
    
    
    //代付接口FastPay2
    'fastpay2_daifu_url'=>'http://www.fast-pay.cc/gateway.aspx',
    'fastpay2_mch_id'=>'1002729',  //正式  也是  测试
    'fastpay2_key'=>'6bd1737d8d072e72adcc99ccf6c10c25', //正式  也是  测试
    'fastpay2_notify_url'=>'https://www.amznol.com/api/fastpay4',//回调地址
    'fastpay2_return_url'=>'https://www.amznol.com/',
    'fastpay2_type'=>1,//支付类型。1：银行卡 8：UPI
    'fastpay2_version'=>'2.0.2',


    ////代付接口 lepay -  代付
    'lepay_daifu_url'=>'https://payment.lexmpay.com/pay/transfer', //接口地址
    'lepay_key'=>'NP1RDNT5WIVCWO54A7WRB5SJLLFJZAEL', //正式
    'lepay_mch_id'=>'991917010',//商户编号 正式
    // 'lepay_key'=>'NM6DXAEG1TXHOFHRRUVKHA9M6GUSPJ7R', //测试
    // 'lepay_mch_id'=>'914914914',//商户编号 测试
    'lepay_notify_url'=>'https://www.amznol.com/api/lepay2',//回调地址
    'lepay_return_url'=>'https://www.amznol.com/',
    'lepay_pay_type'=>'UPI原生一类',//支付类型
    'lepay_bank_code'=>'2220',//通道编码
    
    
     //充值接口htpay
    'htpay_url'=>'https://pay.xlpay888.com/payment/payout', //接口地址
    // 'htpay_key'=>'D69hYQsdYL6TTVWV0icG',  //key  正式
    // 'htpay_mch_id'=>'ht207',//商户编号   正式
    'htpay_key'=>'8tuQuahoSwfhRLmEQS2X',  //key  测试
    'htpay_mch_id'=>'xl006',//商户编号   测试
    'htpay_notify_url'=>'https://www.smsxnbd.top/api/htpay2',//回调地址
    'htpay_return_url'=>'https://www.smsxnbd.top/',
    'htpay_pay_type'=>'UPI原生一类',//支付类型
    'htpay_bank_code'=>'2202',//通道编码
    
    
   
    
    //充值接口 Onepay
    'onepay_url'=>'https://api-bdt.onepay.news/api/v1/order/out',//接口地址
   
    'onepay_key'=>'18nw3I45ya',  //key  正式
    'onepay_mch_id'=>'amznol',//商户编号   正式
    'onepay_aes'=>'yE5ix5Zz3U2gI27G',//aes key
    'onepay_notify_url'=>'https://www.smsxnbd.top/api/onepay2',//回调地址
    'onepay_return_url'=>'https://www.smsxnbd.top/',
    'onepay_pay_type'=>'2',//支付类型
    'onepay_bank_code'=>'B9081',//通道编码
    'onepay_pay_code'=>'B9081',//pay code
    
    
    
    
    
     //充值接口 inrpay
    'inrpay_url'=>'https://inrpay.shop/Payment_Dfpay_add.html',//接口地址
    
    
    // 'mpay_key'=>'b61b09bb53248fbd4358d0a84ce6afc2',  //key  正式
    // 'mpay_mch_id'=>'1713782311828615170',//商户编号   正式
    
    'inrpay_key'=>'wvof6hvglwee9qznkbl4qxza4cp06hik',  //key  测试
    'inrpay_mch_id'=>'240145422',//商户编号   测试
    // 'mpay_aes'=>'yE5ix5Zz3U2gI27G',//aes key
    'inrpay_notify_url'=>'https://www.tonama.top/api/inrpay2',//回调地址
    'inrpay_return_url'=>'https://www.tonama.top/',
    'inrpay_pay_type'=>'UPI原生一类',//支付类型
    'inrpay_bank_code'=>'10005',//通道编码
    'inrpay_bank_type'=>'BANK',//通道编码
    'inrpay_pay_code'=>'1',//pay code
];
