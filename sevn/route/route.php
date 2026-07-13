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
Route::rule('user/:uid','api/UserController/read');
Route::rule('user','api/UserController/read');
Route::rule('api/re51','api/RechargeController/re51'); // pay51代收回调api
Route::rule('api/re512','api/WithdrawController/re51');//pay51代付回调
Route::rule('api/dzxum','api/RechargeController/dzxum'); // dzxum代收回调api
Route::rule('api/dzxum2','api/WithdrawController/dzxum');//dzxum代付回调
Route::rule('api/fastpay','api/RechargeController/FastPay'); // pay51代收回调api
Route::rule('api/fastpay2','api/WithdrawController/FastPay'); // pay51代收回调api
Route::rule('api/htpay','api/RechargeController/HtPay0'); // htpay代收回调api
Route::rule('api/htpay2','api/WithdrawController/HtPay'); // htpay代收回调api
Route::rule('api/sunpay','api/RechargeController/sunpay'); // sunpay代收回调api
Route::rule('api/sunpay2','api/WithdrawController/sunpay');//sunpay代付回调
Route::rule('api/fastpay3','api/RechargeController/FastPay2'); // pay51代收回调api
Route::rule('api/fastpay4','api/WithdrawController/FastPay2'); // pay51代收回调api
Route::rule('api/lepay','api/RechargeController/LePay'); // pay51代收回调api
Route::rule('api/lepay2','api/WithdrawController/LePay'); // pay51代收回调ap0i
Route::rule('api/onepay','api/RechargeController/onepay'); // pay51代收回调api
Route::rule('api/onepay2','api/WithdrawController/onepay'); // pay51代收回调ap0i
Route::rule('api/onepay3','api/RechargeController/onepay2'); // pay51代收回调ap0i
Route::rule('api/mpay','api/RechargeController/mpay'); // pay51代收回调api
Route::rule('api/mpay2','api/WithdrawController/mpay'); // pay51代收回调ap0i inrpay
Route::rule('api/inrpay','api/RechargeController/inrpay'); // pay51代收回调api
Route::rule('api/inrpay2','api/WithdrawController/inrpay'); // pay51代收回调ap0i 

//Route::domain('www.myadmin.cc', function () {
//// 动态注册域名的路由规则
//    Route::rule('admin', '404');
//});
//
//
Route::domain('zs5sys99nj.winxapp.cn', function () {
// 动态注册域名的路由规则
    Route::any('index', '404');
    Route::any('/', 'http://www.winxapp.cn');
    Route::any('index/login','404');
    Route::any('index/regist','404');
    Route::any('index/resetpass','404');
    Route::any('home/index','404');
    Route::any('index/index/login','404');
});

Route::domain('www.zhanshen5.com', function () {
// 动态注册域名的路由规则
    Route::any('admin', '/404.html');
});

Route::domain('zhanshen5.com', function () {
// 动态注册域名的路由规则
    Route::any('admin', '/404.html');
});

Route::domain('www.zhanshen5.cn', function () {
// 动态注册域名的路由规则
    Route::any('admin', '/404.html');
});

Route::domain('zhanshen5.cn', function () {
// 动态注册域名的路由规则
    Route::any('admin', '/404.html');
});

//Route::rule('index/login','index/index/login');
//Route::rule('index/loginauto','index/index/loginauto');
//Route::rule('index/regist','index/index/regist');
//Route::rule('index/h5_reg','index/index/h5_reg');
//Route::rule('index/resetpass','index/index/resetpass');
//Route::rule('home/index','index/home/index');

return [

];