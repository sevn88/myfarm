<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 4:25
 * QQ:1467572213
 */

namespace app\index\model;

use app\index\model\Recharge as Recharge_m;
use Endroid\QrCode\Bundle\DependencyInjection\Configuration;
use think\Model;
use think\Db;
use think\facade\Config;
use think\facade\Request;

class Recharge extends Model
{
    public static function recharge_api()
    {
        return Db::name('fd_recharge_api')->select();
    }

    public static function oceanpay($user_info, $money)
    {
        $oceanpay_url = Config::get('oceanpay_url');
        $oceanpay_code = Config::get('oceanpay_code');
        $oceanpay_key = Config::get('oceanpay_key');
        $oceanpay_paycode = Config::get('oceanpay_paycode');

        $merordercode = time() . mt_rand(11111, 99999); //订单号
        $callbackurl = Request::domain() . '/index/notify/oceanpay';
        $notifyurl = Request::domain() . '/index/recharge/index';

        $sign_data['code'] = $oceanpay_code;
        $sign_data['merordercode'] = $merordercode;
        $sign_data['notifyurl'] = $notifyurl;
        $sign_data['callbackurl'] = $callbackurl;
        $sign_data['amount'] = $money;
        $sign_data['key'] = $oceanpay_key;
        $sign = self::oceanpay_sign($sign_data);
        $params['code'] = $oceanpay_code;
        $params['paycode'] = $oceanpay_paycode;
        $params['notifyurl'] = $notifyurl;
        $params['amount'] = $money;
        $params['callbackurl'] = $callbackurl;
        $params['merordercode'] = $merordercode;
        $params['signs'] = $sign;
        $params['starttime'] = self::getMillisecond();
        $res = self::oceanpay_post($oceanpay_url, $params);
        if ($res['success'] == true) {
            $info['order_id'] = $merordercode;
            $info['uid'] = $user_info['id'];
            $info['pid'] = $user_info['pid'];
            $info['username'] = $user_info['username'];
            $info['phone'] = $user_info['phone'];
            $info['money'] = $money;
            $info['service_charge'] = $money * Base::get_config('service_cz') / 100;
            $info['type'] = 1;
            $info['status'] = 0;
            $info['create_time'] = time();
            $add = Db::name('fd_recharge')->insert($info);
            if ($add == true) {
                return ['code' => 1, 'data' => $res['data']['checkstand']];
            } else {
                return ['code' => 0, 'msg' => '系统错误'];
            }
        } else {
            return ['code' => 0, 'msg' => '接口失败'];
        }
    }

    public static function oceanpay_sign($sign_data)
    {
        $hmacstr = 'code=' . $sign_data['code'] . '&merordercode=' . $sign_data['merordercode'] . '&notifyurl=' . $sign_data['notifyurl'] . '&callbackurl=' . $sign_data['callbackurl'] . '&amount=' . $sign_data['amount'] . '&key=' . $sign_data['key'] . '';
        $sign = strtoupper(md5($hmacstr));
        return $sign;
    }

    public static function oceanpay_post($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $output = curl_exec($ch);
        curl_close($ch);
        $rst = json_decode($output, true);
        return $rst;
    }


    public static function shineupay($user_info, $money)
    {
        $shineupay_id = Config::get('shineupay_id');
        $shineupay_key = Config::get('shineupay_key');
        $notifyUrl = Request::domain() . '/index/notify/shineupay';
        $redirectUrl = Request::domain() . '/index/recharge/index';

        //参数数据处理
        $url = Config::get('shineupay_url'); //网关地址
        $params["orderId"] = time() . mt_rand(11111, 99999); //订单号
        $params["amount"] = $money; //支付金额
        $getMillisecond = self::getMillisecond(); //毫秒时间戳
        $params["details"] = "details_pay";
        $params["userId"] = mt_rand(1111, 9999);
        $params["notifyUrl"] = $notifyUrl;
        $params["redirectUrl"] = $redirectUrl;
        //组装数据63
        $data['body'] = $params;
        $data['merchantId'] = $shineupay_id;
        $data['timestamp'] = $getMillisecond;

        //报文签名
        $sign = self::sign($shineupay_key, $data);
        //封装请求头
        $headers = array("Content-type: application/json;charset=UTF-8", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache", "Api-Sign:$sign");
        //发起post请求
        $json = Recharge_m::shineupay_post($url, $data, 5, $headers, $getMillisecond);
        $res = json_decode($json, true);
        if ($res['status'] == 0 && $res['merchantId'] == $shineupay_id) {
            $order_info['order_id'] = $params["orderId"];
            $order_info['p_order_id'] = $res['body']['transactionId'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
            $order_info['service_charge'] = $money * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return $res['body']['content'];
            } else {
                return 'error';
            }
        } else {
            return 'error';
        }
    }


    public static function sign($key, $data)
    {
        $data = array_filter($data);
        $str = json_encode($data) . "|" . $key;
        $sign = MD5($str);
        return $sign;
    }

    //获取毫秒时间戳
    public static function getMillisecond()
    {
        list($microsecond, $time) = explode(' ', microtime()); //' '中间是一个空格
        return (float)sprintf('%.0f', (floatval($microsecond) + floatval($time)) * 1000);
    }

    public static function shineupay_post($url, $data, $timeout, $headers, $getMillisecond)
    {
        $data = json_encode($data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Errno' . curl_error($curl); //捕抓异常
        }
        curl_close($curl);
        return $output;
    }

    public static function pay51($user_info, $money)
    {

        $post_data['merchantNo'] = 'M221206145930002337';
        $post_data['merchantOrderId'] = time() . mt_rand(11111, 99999); //订单号;
        $post_data['channelCode'] = 'YDFASTDS108';
        $post_data['amount'] = $money * 100;
        $post_data['currency'] = 'INR';
//        $post_data['subject']=$user_info['username'];
        $post_data['email'] = 'amazon@gmail.com';//$user_info['email'];
        $post_data['userName'] = $user_info['username'];
        $post_data['mobileNo'] ="16888";// $user_info['phone'];
        $post_data['expireTime'] = 60;
        $post_data['notifyUrl'] = "https://www.amazon606.com/api/re51";
        ksort($post_data);
        $sign_str = '';
        foreach ($post_data as $pk => $pv) {
            $sign_str .= "{$pk}={$pv}&";
        }
        $sign_str .= "key=c7aa02e69235484faeb4b22a186c1aa7";
        $sign_str_51 = $sign_str;
//        $post_data["temp"]=$sign_str;
        $sign_str = hash_hmac('sha256', $sign_str, 'c7aa02e69235484faeb4b22a186c1aa7');
        $post_data['subject'] = $user_info['username'];

        $post_data['sign'] = $sign_str;
//        return ['code'=>1,'msg'=>'ok','data'=>$post_data]; //为了调试这个地方增加的临时性返回，不要删除

        $re = self::http_data('https://api.eggoout.com/payin/unifiedorder.do',$post_data,true);


        $result = $re;// json_decode($re, true);
        if ($result['code'] == "000") {
            $order_info['order_id'] = $post_data["merchantOrderId"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['data']['sysOrderId'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'Success', 'data' =>$result['data']['checkStand']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.','data'=>$result,'sign_str'=>$sign_str,'sign_str_temp'=>$sign_str_51];
        }

        return ['code' => 1, 'msg' => 'a', 'data' => $re];


    }

    public static function dzxum($user_info, $money)
    {
        $post_data['mch_id'] = Config::get('dzxum_mch_id');
        $post_data['version']="1.0";
        $post_data['notify_url']=Config::get('dzxum_notify_url');
        $post_data['page_url']=Config::get("dzxum_return_url");
        $post_data['mch_order_no'] ='U'. time() . mt_rand(11111, 99999); //订单号;
        $post_data['pay_type']=Config::get('dzxum_bank_code');
        $post_data['trade_amount'] = $money;
        $post_data['order_date']=date('Y-m-d H:i:s') ;//$datetime->format('Y-m-d H:i:s');
//        $post_data['bank_code']=Config::get('dzxum_bank_code');
        $post_data['goods_name']=$user_info['username'];
        $post_data['mch_return_msg'] = $user_info['id'];
        if ($user_info['phone_number']) $post_data['payer_phone'] =$user_info['phone_number'];

        ksort($post_data);
        $sign_str = '';
        foreach ($post_data as $pk => $pv) {
            if ($sign_str==''){
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str = $sign_str."&key=".Config::get('dzxum_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtolower(md5($sign_str));

        $post_data['sign_type']='MD5';
        $post_data['sign']=$sign_str;

        $post_url = Config::get('dzxum_url');


        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$post_url); //支付请求地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response=curl_exec($ch);

        //$res=simplexml_load_string($response);

        curl_close($ch);

//        return ['code' => 0, 'msg' => 'Error.Please contact your customer.','post_data'=>$post_data,'data'=>$response,'sign_str_temp'=>$sign_str_temp,'sign_str'=>$sign_str];

        $result = json_decode($response,true);// json_decode($re, true);
        if ($result['respCode'] == "SUCCESS") {
            $order_info['order_id'] = $post_data["mch_order_no"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['orderNo'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'Success', 'data' =>$result['payInfo']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.','data'=>$response,'sign_str_temp'=>$sign_str_temp,'sign_str'=>$sign_str];
        }




    }
    public static function dzxum2($user_info, $money)
    {
        $post_data['mch_id'] = Config::get('dzxum_mch_id');
        $post_data['version']="1.0";
        $post_data['notify_url']=Config::get('dzxum_notify_url');
        $post_data['page_url']=Config::get("dzxum_return_url");
        $post_data['mch_order_no'] ='U'. time() . mt_rand(11111, 99999); //订单号;
        $post_data['pay_type']=Config::get('dzxum_bank_code2');
        $post_data['trade_amount'] = $money;
        $post_data['order_date']=date('Y-m-d H:i:s') ;//$datetime->format('Y-m-d H:i:s');
//        $post_data['bank_code']=Config::get('dzxum_bank_code2');
        $post_data['goods_name']=$user_info['username'];
        $post_data['mch_return_msg'] = $user_info['id'];
        if ($user_info['phone_number']) $post_data['payer_phone'] =$user_info['phone_number'];

        ksort($post_data);
        $sign_str = '';
        foreach ($post_data as $pk => $pv) {
            if ($sign_str==''){
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str = $sign_str."&key=".Config::get('dzxum_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtolower(md5($sign_str));

        $post_data['sign_type']='MD5';
        $post_data['sign']=$sign_str;

        $post_url = Config::get('dzxum_url');


        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$post_url); //支付请求地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response=curl_exec($ch);

        //$res=simplexml_load_string($response);

        curl_close($ch);

//        return ['code' => 0, 'msg' => 'Error.Please contact your customer.','post_data'=>$post_data,'data'=>$response,'sign_str_temp'=>$sign_str_temp,'sign_str'=>$sign_str];

        $result = json_decode($response,true);// json_decode($re, true);
        if ($result['respCode'] == "SUCCESS") {
            $order_info['order_id'] = $post_data["mch_order_no"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['orderNo'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'Success', 'data' =>$result['payInfo']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.','data'=>$response];
        }




    }


public static function FastPay($user_info, $money)
    {
        $post_data['merchantNo'] = Config::get('fastpay_mch_id');
        $post_data['orderNo'] ='F'. time() . mt_rand(11111, 99999); //订单号;
        $post_data['amount'] = $money;
        $post_data['type']= Config::get("fastpay_type");
        $post_data['notifyUrl']=Config::get("fastpay_notify_url");
        $post_data['userName']=$user_info['username'];
        $post_data['ext']=$user_info['id'];
        $post_data['version']=Config::get("fastpay_version");
        
      

        ksort($post_data);
        $sign_str = '';
        foreach ($post_data as $pk => $pv) {
            if ($sign_str==''){
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str = $sign_str."&key=".Config::get('fastpay_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtoupper(md5($sign_str));

        $post_data['sign']=$sign_str;

        $post_url = Config::get('fastpay_url');


        $response=self::http_data($post_url,$post_data,true);
        

        //$res=simplexml_load_string($response);

        

//        return ['code' => 0, 'msg' => 'Error.Please contact your customer.','post_data'=>$post_data,'data'=>$response,'sign_str_temp'=>$sign_str_temp,'sign_str'=>$sign_str];

        $result = $response;// json_decode($re, true);
        if ($result['code'] == "0") {
            $order_info['order_id'] = $post_data["orderNo"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['platformOrderNo'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'Success', 'data' =>$result['url']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.','data'=>$response];
        }




    }



//----------* htpay  start ---------------------
public static function htpay($user_info, $money)
    {
        //要求填写真实的付款账号，所以这里先去检测账号是否真实绑定。如果没有绑定，返回错误
        $post_data['merchantLogin'] = Config::get('htpay_mch_id');
        $post_data['orderCode'] = 'H'. time() . mt_rand(11111, 99999); //订单号;
        $post_data['currencyCode'] = 'TK';  //孟加拉固定TK
        $post_data['account'] = "123456";//$user_info['account_number'];
        $post_data['amount'] = $money;
        $post_data['notifyUrl'] = Config::get("htpay_notify_url");
        $post_data['name'] = "joneCorner";
        $post_data['email'] = "joneCorner@gmail.com"; //$user_info['email'];
        $post_data['phone'] = "654321";//$user_info['phone_number'];
        $sign_str =  $post_data['orderCode'].Config::get('htpay_key');
        $sign_str = md5($sign_str);
        $post_data['sign'] = $sign_str;
        
        $post_url = Config::get('htpay_url');
        $response=self::http_data($post_url,$post_data,true);
        //$res=simplexml_load_string($response);
       // return ['code' => 0, 'msg' => 'Error.Please contact your customer.','post_data'=>$post_data,'data'=>$response,'sign_str'=>$sign_str];
        // var_dump($response);die;
        $result = $response;// json_decode($re, true);
        if ($result['platformOrderCode'] ) {
            $order_info['order_id'] = $post_data["orderCode"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['platformOrderCode'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $order_info['agent_id']=$user_info['agent_id'];
            $order_info['agent_username']=$user_info['agent_username'];
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'Success', 'data' =>$result['paymentUrl']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.','data'=>$response];
        }
    }
//----------* htpay  end ---------------------

//----------* fastpay2  start ---------------------
public static function fastpay2($user_info, $money)
    {
        $post_data['mer_no'] = Config::get('fastpay2_mch_id');
        $post_data['order_no'] = 'F'. time() . mt_rand(11111, 99999); //订单号;
        $post_data['order_amount'] = $money;
        $post_data['payname'] = $user_info['username'];
        $post_data['payemail'] ="test123@gmail.com";// $user_info['email'];
        $post_data['payphone'] = "1234567890";//$user_info['phone_number'];
        $post_data['currency'] = 'BDT';
        $post_data['paytypecode'] = '25001';
        $post_data['method'] = 'trade.create';
        $post_data['returnurl']  =Config::get("fastpay2_notify_url");
        $post_data['pageurl'] = Config::get("fastpay2_return_url");
        
        ksort($post_data);
        $sign_str = '';
        foreach ($post_data as $pk => $pv) {
            if ($sign_str==''){
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str = $sign_str.Config::get('fastpay2_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtolower(md5($sign_str));

        $post_data['sign']=$sign_str;

        $post_url = Config::get('fastpay2_url');


        $response=self::http_data($post_url,$post_data,true);
        

        //$res=simplexml_load_string($response);
        // return ['code' => 0, 'msg' => 'Error.Please contact your customer.','post_data'=>$post_data,'data'=>$response,'sign_str_temp'=>$sign_str_temp,'sign_str'=>$sign_str];

        $result = $response;// json_decode($re, true);
        if ($result['status'] == "success") {
            $order_info['order_id'] = $post_data["order_no"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['mer_no'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'Success', 'data' =>$result['order_data']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.','data'=>$response];
        }
    }
    
//----------* fastpay2  end ---------------------


public static function htpay2($user_info, $money)
    {
        //要求填写真实的付款账号，所以这里先去检测账号是否真实绑定。如果没有绑定，返回错误
        $post_data['merchantLogin'] = Config::get('htpay_mch_id');
        $post_data['orderCode'] = 'H'. time() . mt_rand(11111, 99999); //订单号;
        $post_data['currencyCode'] = 'TK';  //孟加拉固定TK
        $post_data['account'] = "123456";//$user_info['account_number'];
        $post_data['amount'] = $money;
        $post_data['notifyUrl'] = Config::get("htpay_notify_url");
        $post_data['name'] = "joneCorner";
        $post_data['email'] = "joneCorner@gmail.com"; //$user_info['email'];
        $post_data['phone'] = "654321";//$user_info['phone_number'];
        $sign_str =  $post_data['orderCode'].Config::get('htpay_key');
        $sign_str = md5($sign_str);
        $post_data['sign'] = $sign_str;
        
        $post_url = Config::get('htpay_url');
        $response=self::http_data($post_url,$post_data,true);
        //$res=simplexml_load_string($response);
       // return ['code' => 0, 'msg' => 'Error.Please contact your customer.','post_data'=>$post_data,'data'=>$response,'sign_str'=>$sign_str];
        // var_dump($response);die;
        $result = $response;// json_decode($re, true);
        if ($result['platformOrderCode'] ) {
            $order_info['order_id'] = $post_data["orderCode"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['platformOrderCode'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'Success', 'data' =>$result['paymentUrl']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.','data'=>$response];
        }
    }


//----------* sunpay  start ---------------------
public static function sunpay($user_info, $money)
    {
        $post_data['mch_id'] = Config::get('sunpay_mch_id');
        $post_data['version']="1.0";
        $post_data['notify_url']=Config::get('sunpay_notify_url');
        $post_data['page_url']=Config::get("sunpay_return_url");
        $post_data['mch_order_no'] ='S'. time() . mt_rand(11111, 99999); //订单号;
        $post_data['pay_type']=Config::get('sunpay_bank_code');
        $post_data['trade_amount'] = $money;
        $post_data['order_date']=date('Y-m-d H:i:s') ;//$datetime->format('Y-m-d H:i:s');
//        $post_data['bank_code']=Config::get('dzxum_bank_code2');
        $post_data['goods_name']=$user_info['username'];
        // $post_data['mch_return_msg'] = $user_info['id'];
        if ($user_info['phone_number']) $post_data['payer_phone'] =$user_info['phone_number'];

        ksort($post_data);
        $sign_str = '';
        foreach ($post_data as $pk => $pv) {
            if ($sign_str==''){
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str = $sign_str."&key=".Config::get('sunpay_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtolower(md5($sign_str));

        $post_data['sign_type']='MD5';
        $post_data['sign']=$sign_str;

        $post_url = Config::get('sunpay_url');


        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$post_url); //支付请求地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response=curl_exec($ch);

        //$res=simplexml_load_string($response);

        curl_close($ch);

        // return ['code' => 0, 'msg' => 'Error.Please contact your customer.','post_data'=>$post_data,'data'=>$response,'sign_str_temp'=>$sign_str_temp,'sign_str'=>$sign_str];

        $result = json_decode($response,true);// json_decode($re, true);
        if ($result['respCode'] == "SUCCESS") {
            $order_info['order_id'] = $post_data["mch_order_no"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['orderNo'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'SUCCESS', 'data' =>$result['payInfo']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.','data'=>$response];
        }




    }

//----------* sunpay  end ---------------------


//----------* lepay  start ---------------------
public static function lepay($user_info, $money)
    {
        $post_data['mch_id'] = Config::get('lepay_mch_id');
        $post_data['version']="1.0";
        $post_data['notify_url']=Config::get('lepay_notify_url');
        $post_data['page_url']=Config::get("lepay_return_url");
        $post_data['mch_order_no'] ='L'. time() . mt_rand(11111, 99999); //订单号;
        $post_data['pay_type']=Config::get('lepay_bank_code');
        $post_data['trade_amount'] = $money;
        $post_data['order_date']=date('Y-m-d H:i:s') ;//$datetime->format('Y-m-d H:i:s');
//        $post_data['bank_code']=Config::get('dzxum_bank_code2');
        $post_data['goods_name']=$user_info['username'];
        // $post_data['mch_return_msg'] = $user_info['id'];
        if ($user_info['phone_number']) $post_data['payer_phone'] =$user_info['phone_number'];

        ksort($post_data);
        $sign_str = '';
        foreach ($post_data as $pk => $pv) {
            if ($sign_str==''){
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str = $sign_str."&key=".Config::get('lepay_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtolower(md5($sign_str));

        $post_data['sign_type']='MD5';
        $post_data['sign']=$sign_str;

        $post_url = Config::get('lepay_url');


        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$post_url); //支付请求地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response=curl_exec($ch);

        //$res=simplexml_load_string($response);

        curl_close($ch);

        // return ['code' => 0, 'msg' => 'Error.Please contact your customer.','post_data'=>$post_data,'data'=>$response,'sign_str_temp'=>$sign_str_temp,'sign_str'=>$sign_str];

        $result = json_decode($response,true);// json_decode($re, true);
        if ($result['respCode'] == "SUCCESS") {
            $order_info['order_id'] = $post_data["mch_order_no"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['orderNo'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'SUCCESS', 'data' =>$result['payInfo']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.','data'=>$response];
        }




    }

//----------* lepay  end ---------------------

//----------* mpay  start ---------------------
public static function mpay($user_info, $money)
    {
        $post_data['merchantNo'] = Config::get('mpay_mch_id');
        $post_data['orderNo'] = 'M'. time() . mt_rand(11111, 99999); //订单号;
        $post_data['amount'] = strval($money);
        $post_data['type']=8;
        $post_data['notifyUrl']=Config::get('mpay_notify_url');
        $post_data['userName']='John';
        $post_data['ext']='test';
        $post_data['version']="2.0.2";
        
        
        ksort($post_data);
        $sign_str = '';
        foreach ($post_data as $pk => $pv) {
            if ($sign_str==''){
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str = $sign_str."&key=".Config::get('mpay_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtoupper(md5($sign_str));

        $post_data['sign']=$sign_str;

        $post_url = Config::get('mpay_url');
        
        $response=self::http_data($post_url,$post_data,true);

        return ['code' => 0, 'msg' => 'Error.Please contact your customer.','post_data'=>$post_data,'data'=>$response,'sign_str_temp'=>$sign_str_temp,'sign_str'=>$sign_str];

        $result =$response; //json_decode($response,true);// json_decode($re, true);
        if ($result['code'] == 0) {
            $order_info['order_id'] = $post_data["orderNo"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['merchantNo'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'SUCCESS', 'data' =>$result['url']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.'.$result['message'],'data'=>$response];
        }




    }

//----------* mpay  end ---------------------

//----------* inrpay  start ---------------------
public static function inrpay($user_info, $money)
    {
        $post_data['pay_memberid'] = Config::get('inrpay_mch_id');
        $post_data['pay_orderid'] = 'INR'. time() . mt_rand(11111, 99999); //订单号;
        $post_data['pay_amount'] = strval($money);
        $post_data['pay_applydate'] =date('Y-m-d H:i:s');
        $post_data['pay_bankcode']= Config::get('inrpay_pay_code');
        $post_data['pay_notifyurl']=Config::get('inrpay_notify_url');
        $post_data['pay_callbackurl']=Config::get('inrpay_return_url');
        
        
       
        
        
        ksort($post_data);
        $sign_str = '';
        foreach ($post_data as $pk => $pv) {
            if ($sign_str==''){
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str = $sign_str."&key=".Config::get('inrpay_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtoupper(md5($sign_str));

        $post_data['pay_md5sign']=$sign_str;
        $post_data['pay_productname']='goods';

        $post_url = Config::get('inrpay_url');
        
        // $response=self::http_data($post_url,$post_data,true);
        
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$post_url); //支付请求地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response=curl_exec($ch);

        //$res=simplexml_load_string($response);

        curl_close($ch);

        // return ['code' => 0, 'msg' => 'Error.Please contact your customer.','post_data'=>$post_data,'data'=>$response,'sign_str_temp'=>$sign_str_temp,'sign_str'=>$sign_str];

        $result =json_decode($response,true);// json_decode($re, true);
        // var_dump($result);die;
        if ($result['status'] == "success") {
            $order_info['order_id'] = $post_data["pay_orderid"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['sys_orderId'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $order_info['agent_id']=$user_info['agent_id'];
            $order_info['agent_username']=$user_info['agent_username'];
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'SUCCESS', 'data' =>$result['pay_url']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.'.$result['msg'],'data'=>$response];
        }




    }

//----------* inrpay  end ---------------------

//----------* wakapay  start ---------------------
public static function wakapay($user_info, $money)
    {
        $post_data['mer_no'] = Config::get('wakapay_mch_id');
        $post_data['order_no'] = 'WA'. time() . mt_rand(11111, 99999); //订单号;
        $post_data['order_amount'] = $money;
        $post_data['payname'] = $user_info['username'];
        $post_data['payemail'] ="test123@gmail.com";// $user_info['email'];
        $post_data['payphone'] = "1234567890";//$user_info['phone_number'];
        $post_data['currency'] = 'ZAR';
        $post_data['paytypecode'] = Config::get('wakapay_bank_code');
        $post_data['method'] = 'trade.create';
        $post_data['returnurl']  =Config::get("wakapay_notify_url");
        // $post_data['pageurl'] = Config::get("fastpay2_return_url");
        
        ksort($post_data);
        $sign_str = '';
        foreach ($post_data as $pk => $pv) {
            if ($sign_str==''){
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str = $sign_str.Config::get('wakapay_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtolower(md5($sign_str));

        $post_data['sign']=$sign_str;

        $post_url = Config::get('wakapay_url');


        $response=self::http_data($post_url,$post_data,true);
        

        //$res=simplexml_load_string($response);
        return ['code' => 0, 'msg' => 'Error.Please contact your customer.','post_data'=>$post_data,'data'=>$response,'sign_str_temp'=>$sign_str_temp,'sign_str'=>$sign_str];

        $result = $response;// json_decode($re, true); 
        if ($result['status'] == "success") {
            $order_info['order_id'] = $post_data["order_no"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['mer_no'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'Success', 'data' =>$result['order_data']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.','data'=>$response];
        }
    }
    
//----------* wakapay  end ---------------------


//----------* onepay  start ---------------------
public static function onepay($user_info, $money,$pay_type)
    {
        
        $post_data['orderNo'] ='O'. time() . mt_rand(11111, 99999); //订单号;
        $post_data['payCode']=Config::get('onepay_pay_code');
        $post_data['amount']=intval($money*100);
        $post_data['notifyUrl']=Config::get("onepay_notify_url");
        $post_data['returnUrl']  =Config::get("onepay_return_url");
        $post_data['payerType']=$pay_type;
        $post_url = Config::get("onepay_url");
        $data_temp = self::encryptionAes($post_data);
        $data = ["data"=>$data_temp];
        $response = self::curlPost($post_url,$data);


        
        

        //$res=simplexml_load_string($response);
        // return ['code' => 0, 'msg' => 'Error.Please contact your customer.','post_data'=>$post_data,'data'=>$response,'sign_str_temp'=>$data,'sign_str'=>$data_temp];

        $result =  json_decode($response, true); 
        if ($result['code'] == 200) {
            $order_info['order_id'] = $post_data["orderNo"];   //$result['data']['order_sn'];
            $order_info['p_order_id'] = $result['data']['merchantNo'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $order_info['agent_id']=$user_info['agent_id'];
            $order_info['agent_username']=$user_info['agent_username'];
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => 'Success', 'data' =>$result['data']['paymentUrl']];
            } else {
                return ['code' => 0, 'msg' => 'Error.Please contact your customer.'];
            }
        } else {
            return ['code' => 0, 'msg' => 'Error.Recharge Error.Please contact your customer.'.$result['message'],'data'=>$response];
        }
    }
    
//----------* wakapay  end ---------------------


/**加密
     * @param array $data
     * @return string
     */
    public static function encryptionAes(array $data)
    {
//        $jsonData = json_encode($data,true);
        //修改
        $method='AES-128-CBC'; //AES加密定义不要更改
        $password=Config::get("onepay_aes"); //AES密钥
        $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE );
        
        $aesSecret = bin2hex(openssl_encrypt($jsonData, $method,$password,  OPENSSL_RAW_DATA, $password));
        return $aesSecret;
    }


/**post请求  只针对 onepay
     * @param string $url
     * @param array $data
     * @return false|string
     */
    public static function curlPost($url = '', $data=null)
    {
        $authorizationKey=Config::get("onepay_key");
        $ch = curl_init();//初始化
        curl_setopt($ch, CURLOPT_URL, $url);//访问的URL
        curl_setopt($ch, CURLOPT_POST, true);//请求方式为post请求
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//只获取页面内容，但不输出
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//https请求 不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//https请求 不验证HOST
        $header = [
            'Content-type: application/json;charset=UTF-8',
            'Authorization: '.$authorizationKey,
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));//请求数据
        $result = curl_exec($ch);//执行请求
        curl_close($ch);//关闭curl，释放资源
        return $result;
    }
    
    
    
    public static function http_data($url, $data = NULL, $json = false)
    {
        // Content-Type:application/json  用这种函数
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        if (!empty($data)) {
            if ($json && is_array($data)) {
                $data = json_encode($data);
            }
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            if ($json) {
                // 发送JSON数据                　　　　
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_HTTPHEADER,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($data)
                ));
            }
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        $errorno = curl_errno($curl);
        if ($errorno) {
            return array('errorno' => false, 'errmsg' => $errorno);
        }
        curl_close($curl);
        return json_decode($res, true);
    }
    
    


    public static function Global_pay($user_info, $money)
    {
        $post_data['merch_id'] = '80';
        $post_data['payment_id'] = 3;
        $post_data['order_sn'] = time() . mt_rand(11111, 99999); //订单号
        $post_data['amount'] = $money;
        $post_data['notify_url'] = Request::domain() . '/index/notify/global_pay';
        //按字典正序排序传入的参数
        ksort($post_data);

        $sign_str = '';
        foreach ($post_data as $pk => $pv) {
            $sign_str .= "{$pk}={$pv}&";
        }
        $sign_str .= "key=9716841d1b11cb39c32599f79b686a48f4a1e01f";
        $post_data['sign'] = md5($sign_str);

        //开始提交--------------------------------------------------------------
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://globalpay.pw/api/payTest/order');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($output, true);
        if ($result['code'] == 1) {
            $order_info['order_id'] = $result['data']['order_sn'];
            $order_info['p_order_id'] = $result['data']['pt_order_sn'];
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
            $order_info['service_charge'] = $money * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            if ($add_order == true) {
                return ['code' => 1, 'msg' => '下单成功', 'data' => $result['data']['pay_pageurl']];
            } else {
                return ['code' => 0, 'msg' => '系统错误,请联系客服!'];
            }
        } else {
            return ['code' => 0, 'msg' => '创建订单失败'];
        }
    }

    
     public static function xianxia($user_info, $money)
    {
        
        
            $order_info['order_id'] ='XA'. time() . mt_rand(11111, 99999); //订单号;   //$result['data']['order_sn'];
            $order_info['p_order_id'] = '';
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            // $temp = Db::getLastInsID();
            // $info = Db::name('fd_recharge')->where('order_id',$order_info['order_id'])->find();
            // var_dump($info);die;
            
            $_SESSION["order_id"]=$order_info['order_id'];
           $_SESSION["xianxia_recharge"]=$money;
           $_SESSION["offline"]='offline1';
            // var_dump(session('order_id'));die;
            if ($add_order == true) {
                return ['code' => 2, 'msg' => 'Success.', 'data' =>$order_info['order_id']];
            } else {
                return ['code' => 0, 'msg' => 'Error. Please contact your customer.'];
            }
        



    }
     public static function xianxia2($user_info, $money)
    {
        
        
            $order_info['order_id'] ='XB'. time() . mt_rand(11111, 99999); //订单号;   //$result['data']['order_sn'];
            $order_info['p_order_id'] = '';
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            // $temp = Db::getLastInsID();
            // $info = Db::name('fd_recharge')->where('order_id',$order_info['order_id'])->find();
            // var_dump($info);die;
            
            $_SESSION["order_id"]=$order_info['order_id'];
           $_SESSION["xianxia_recharge"]=$money;
           $_SESSION["offline"]='offline2';
            // var_dump(session('order_id'));die;
            if ($add_order == true) {
                return ['code' => 2, 'msg' => 'Success.', 'data' =>$order_info['order_id']];
            } else {
                return ['code' => 0, 'msg' => 'Error. Please contact your customer.'];
            }
        



    }
     public static function xianxia3($user_info, $money)
    {
        
        
            $order_info['order_id'] ='XC'. time() . mt_rand(11111, 99999); //订单号;   //$result['data']['order_sn'];
            $order_info['p_order_id'] = '';
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            // $temp = Db::getLastInsID();
            // $info = Db::name('fd_recharge')->where('order_id',$order_info['order_id'])->find();
            // var_dump($info);die;
            
            $_SESSION["order_id"]=$order_info['order_id'];
           $_SESSION["xianxia_recharge"]=$money;
           $_SESSION["offline"]='offline3';
            // var_dump(session('order_id'));die;
            if ($add_order == true) {
                return ['code' => 2, 'msg' => 'Success.', 'data' =>$order_info['order_id']];
            } else {
                return ['code' => 0, 'msg' => 'Error. Please contact your customer.'];
            }
        



    }
     public static function xianxia4($user_info, $money)
    {
        
        
            $order_info['order_id'] ='XD'. time() . mt_rand(11111, 99999); //订单号;   //$result['data']['order_sn'];
            $order_info['p_order_id'] = '';
            $order_info['uid'] = $user_info['id'];
            $order_info['pid'] = $user_info['pid'];
            $order_info['username'] = $user_info['username'];
            $order_info['phone'] = $user_info['phone'];
            $order_info['money'] = $money;
//            $order_info['service_charge'] = $money;// * Base::get_config('service_cz') / 100;
            $order_info['type'] = 1;
            $order_info['status'] = 0;
            $order_info['create_time'] = time();
            $add_order = Db::name('fd_recharge')->insert($order_info);
            // $temp = Db::getLastInsID();
            // $info = Db::name('fd_recharge')->where('order_id',$order_info['order_id'])->find();
            // var_dump($info);die;
            
            $_SESSION["order_id"]=$order_info['order_id'];
           $_SESSION["xianxia_recharge"]=$money;
           $_SESSION["offline"]='offline4';
            // var_dump(session('order_id'));die;
            if ($add_order == true) {
                return ['code' => 2, 'msg' => 'Success.', 'data' =>$order_info['order_id']];
            } else {
                return ['code' => 0, 'msg' => 'Error. Please contact your customer.'];
            }
        



    }
    
    public static function contact_cs(){
        $info = Db::name("system_cs")->where('id',1)->find();
        return $info;
    }
}