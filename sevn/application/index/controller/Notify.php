<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/24
 * Time: 21:27
 * QQ:1467572213
 */

namespace app\index\controller;

use think\Controller;
use think\facade\Config;
use think\facade\Request;
use think\facade\Session;
use \app\index\model\Notify as Notify_m;

class Notify extends Controller
{

    public function global_pay()
    {
        if (Request::isPost()){
            $data = Request::post();
            wlog('data',$data);
            $sign_data['merch_id'] = '80';
            $sign_data['payment_id'] = 3;
            $sign_data['order_sn'] = $data['order_sn'];
            $sign_data['amount'] = $data['amount'];
            $sign_data['notify_url'] = Request::domain().'/index/notify/global_pay';
            ksort($sign_data);
            $sign_str='';
            foreach($sign_data as $pk=>$pv){
                $sign_str.="{$pk}={$pv}&";
            }
            $sign_str.="key=9716841d1b11cb39c32599f79b686a48f4a1e01f";
            $sign = md5($sign_str);
            wlog('sign',$sign);
        }

    }

    public function oceanpay_w()
    {
        if (Request::isPost()){
            $data = Request::post();
            $sign_data['code'] = Config::get('oceanpay_code');
            $sign_data['key'] = Config::get('oceanpay_key');
            $sign_data['amount'] = $data['amount'];
            $sign_data['ifsc'] = $data['ifsc'];
            $sign_data['bankname'] = $data['bankname'];
            $sign_data['accountname'] = $data['accountname'];
            $sign_data['cardnumber'] = $data['cardnumber'];
            $sign_data['starttime'] = $data['starttime'];
            $sign_data['merissuingcode'] = $data['merissuingcode'];
            $sign_data['issuingcode'] = $data['issuingcode'];
            $sign_data['returncode'] = $data['returncode'];
            $sign_data['message'] = $data['message'];
            $sign = self::oceanpay_w_sign($sign_data);
            if ($sign == $data['signs']){
                $res = Notify_m::oceanpay_w($sign_data);
                if ($res == 'success'){
                    echo 'OK';
                }else{
                    echo 'error';
                }
            }else{
                echo 'error_sign';
            }
        }
    }

    public static function oceanpay_w_sign($sign_data)
    {
        $hmacstr = 'accountname='.$sign_data['accountname'].'&amount='.$sign_data['amount'].'&bankname='.$sign_data['bankname'].'&cardnumber='.$sign_data['cardnumber'].'&code='.$sign_data['code'].'&ifsc='.$sign_data['ifsc'].'&issuingcode='.$sign_data['issuingcode'].'&merissuingcode='.$sign_data['merissuingcode'].'&message='.$sign_data['message'].'&returncode='.$sign_data['returncode'].'&starttime='.$sign_data['starttime'].'&key='.$sign_data['key'].'';
        $sign = strtoupper(md5($hmacstr));
        return $sign;
    }

    public function oceanpay()
    {
        if (Request::isPost()){
            $data = Request::post();
//            wlog('post',$post);
//            wlog('data',$data);
            $sign_data['code'] = Config::get('oceanpay_code');
            $sign_data['key'] = Config::get('oceanpay_key');
            $sign_data['terraceordercode'] = $data['terraceordercode'];
            $sign_data['merordercode'] = $data['merordercode'];
            $sign_data['createtime'] = $data['createtime'];
            $sign_data['chnltrxid'] = $data['chnltrxid'];
//            wlog('sign_data',$sign_data);
            $sign = self::oceanpay_sign($sign_data);
//            wlog('sign',$sign);
            if ($data['sign'] == $sign){
                $res = Notify_m::oceanpay($data);
                switch ($res)
                {
                    case 'success':
                        echo 'OK';
                        break;
                    case 'error':
                    case 'error_status':
                    case 'error_money':
                    case 'error_info':
                        echo $res;
                        break;
                }
            }else{
                echo 'FAIL';
            }
        }

    }

    public static function oceanpay_sign($sign_data)
    {
        $hmacstr ='code='.$sign_data['code'].'&key='.$sign_data['key'].'&terraceordercode='.$sign_data['terraceordercode'].'&merordercode='.$sign_data['merordercode'].'&createtime='.$sign_data['createtime'].'&chnltrxid='.$sign_data['chnltrxid'].'';
        $sign = strtoupper(md5($hmacstr));
        return $sign;
    }


    public function shineupay()
    {
        //接受Post值
        $shineupay_key = Config::get('shineupay_key');
        $contents = file_get_contents('php://input');
        //全局参数
        $secret_key = $shineupay_key; //商户密钥
        //报文加密
        $str = $contents . "|" . $secret_key;
        $signr = MD5($str);
        //接受头部header信息
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        //存储头部信息
        //获取签名MD5值
        $sign = $headers['Api-Sign'];
        //验签
        if ($sign != $signr) {
            //验签失败
            echo '签名错误';
            exit;
        }
        $post = json_decode($contents, true);
        //接受参数
        $params['orderId'] = $post['body']['orderId']; //商户单号
        $params['platformOrderId'] = $post['body']['platformOrderId']; //第三方单号
        $status = $params['status'] = $post['body']['status']; //支付状态 0:尚未付款，订单已创建 1:付款成功 2:付款失败，请重新支付（二维码过期，超时付款等）3:付款中，表示等待付款中91	金额异常，支付订单金额出现异常
        if ($params['status'] == 1)  //支付时间
        //判断签名是否正确
        if ($signr == $sign) {
            //执行支付成功
            if ($status == 1) {
                $shineupay = Notify_m::shineupay($params['orderId']);
                switch ($shineupay)
                {
                    case 'success':
                        echo 'success';
                        break;
                    case 'error':
                    case 'error_money':
                    case 'error_order':
                        wlog('shineupay_error',$shineupay);
                        echo 'success';
                        break;
                }
            }
        } else {
            echo 'FAIL'; // 失败
        }
    }

}