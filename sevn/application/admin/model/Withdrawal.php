<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/24
 * Time: 15:26
 * QQ:1467572213
 */

namespace app\admin\model;

use think\facade\Request;
use think\Model;
use think\Db;
use think\facade\Config;
use think\facade\Session;

class Withdrawal extends Model
{
    public static function index($keyword,$status,$userid,$usertel,$order_no)
    {

        if ($keyword || $status!='' || $usertel || $userid  || $order_no) {
//            $where[] =array('agent_check','=',1);
            $where = array();
            if ($keyword){
                $where[]=array('username','like',"%$keyword%");
            }
            if ($order_no){
                $where[]=array('order_id','like',"%$order_no%");
            }
            if ($status!=''){
                if ($status!='99'){
                $where[]=array('status','=',$status);
                }
            }
            if ($userid){
                $where[]=array('uid','like',"%$userid%");
            }
            if ($usertel){
                $where[]=array('phone','like',"%$usertel%");
            }

            $list = Db::name('fd_withdrawal')->where($where)->order('id','desc')
                ->paginate(10, false, ['query' => request()->param()])->each(function($item,$key){
                    $item["pusername"]=Db::name('fd_user')->where('id',$item["pid"])->field('username')->value('username');
                    return $item;});
            // $model = new Model();
            // $sql = "select a.*,b.username as pusername from fd_withdrawal as a,fd_user as b where a.pid=b.id order by id desc limit 10;"
            // $list=$model->query($sql);
            // foreach ($list as $key=>$value) {
            //     $list[$key]['aaa']="aaa";//Db::name('fd_user')->where('id',$value['pid'])->field('username')->select();
            //     // print_r( $list[$key]);
            // }
            // echo Db::getLastSql();echo $status;echo("<br>");
            // print_r($list);
            // die;

            return $list;
        } else {
            $list = Db::name('fd_withdrawal')->order('id', 'desc')
                ->paginate(10, false, ['query' => request()->param()])->each(function($item,$key){
                    $item["pusername"]=Db::name('fd_user')->where('id',$item["pid"])->field('username')->value('username');
                    return $item;});
            // echo Db::getLastSql();echo $status;die;
            return $list;
        }
    }

    public static function get_withdrawal_info($id)
    {
        $info = Db::name('fd_withdrawal')->where('id',$id)->find();
        return $info;
    }
    public static function withdraw_type()
    {
        $withdraw_type = Db::name('fd_withdraw_type')->select();
        return $withdraw_type;
    }

    public static function withdrawal_pass($id)
    {
        $info = Db::name('fd_withdrawal')->where('id',$id)->find();
//        $oceanpay_w_url = Config::get('oceanpay_w_url');
        $pay51_daifu_url = Config::get('pay51_daifu_url');
        $post['merchantNo'] = Config::get('pay51_merchantNo');
        $post['merchantOrderId'] = $info['order_id'];
        $post['channelCode']=Config::get('pay51_channelCode');
        $post['amount'] = $info['actual_money']*100;
        $post['currency']="INR";
        $post['email']=$info['email'];
        $post['userName']=$info['holder_name'];
        $post['mobileNo']=$info['phone_number'];
        $post['notifyUrl']= Config::get('pay51_daifu_huitiao_url');
        $post['expireTime']=60;

        ksort($post);
        $sign_str = '';
        foreach ($post as $pk => $pv) {
            $sign_str .= "{$pk}={$pv}&";
        }
        $sign_str .= "key=".Config::get('pay51_key');
//        $post_data["temp"]=$sign_str;
        $sign_str = hash_hmac('sha256', $sign_str, Config::get('pay51_key'));
        $post['version'] = '1.0';
        $post['bankInfo']='{"bankName":"'.$info['bank_name'].'","cardNumber":"'.$info['account_number'].'","ifsc":"'.$info['ifsc'].'"}';

        $post['sign'] = $sign_str;


        $re = self::http_data($pay51_daifu_url,$post,true);
        // var_dump($re);

        if ($re['code']=='000'){
            if ($re['data']['status']=="SUCCESS" || $re['data']['status']=="NOTPAY"){
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',3); //调用支付接口开始支付，如果成功就返回1，更新status=1
                return 'success';
            }else{
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',5);//调用接口失败，这个时候就要直接是提示是接口拒绝支付
                if ($re['data']['status']=="FAIL") return '调用接口失败';
                if ($re['data']['status']=="CLOSED") return '订单关闭';
            }
        }else
        {
            return $re['msg'];
        }



    }


    public static function withdrawal_pass2($id)
    {
        $info = Db::name('fd_withdrawal')->where('id',$id)->find();

        $dzxum_daifu_url = Config::get('dzxum_daifu_url');
        $post['mch_id'] = Config::get('dzxum_mch_id');
        $post['mch_transferId'] = $info['order_id'];
        $post['transfer_amount'] = $info['actual_money'];
        $post['apply_date']=date('Y-m-d H:i:s', $info['create_time']);
        $post['bank_code']='IDPT0001';
        $post['receive_name']=$info['holder_name'];
        $post['receive_account']=$info['account_number'];
        $post['remark']=$info['ifsc'];
        $post['back_url']=Config::get('dzxum_notify_url');
        $post['receiver_telephone']=$info['phone_number'];


        ksort($post);
        $sign_str = '';
        foreach ($post as $pk => $pv) {
            if ($sign_str=='')
            {
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str .= "&key=".Config::get('dzxum_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtolower(md5($sign_str));
        $post['sign_type'] = 'MD5';

        $post['sign'] = $sign_str;


        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$dzxum_daifu_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response=curl_exec($ch);


        curl_close($ch);
        // var_dump($response);

        $re=json_decode($response,true);
        
        if (isset($re['respCode'])){
            if ($re['respCode']=='SUCCESS'){

                Db::name('fd_withdrawal')->where('id',$id)->setField('status',3); //调用支付接口开始支付，如果成功就返回1，更新status=1
                return 'success';
    
            }else
            {
                // Db::name('fd_withdrawal')->where('id',$id)->setField('status',5);//调用接口失败，这个时候就要直接是提示是接口拒绝支付
                return $re['errorMsg'];
            }

            
        }else{
            return "调用uzpay失败";
        }

        


    }

    public static function withdrawal_pass3($id)
    {
        $info = Db::name('fd_withdrawal')->where('id',$id)->find();
//        $oceanpay_w_url = Config::get('oceanpay_w_url');
        $fastpay_daifu_url = Config::get('fastpay_daifu_url');
        $post['merchantNo'] = Config::get('fastpay_mch_id');
        $post['orderNo'] = $info['order_id'];
        $post['amount'] = $info['actual_money'];
        $post['type']=Config::get("fastpay_type");
        $post['notifyUrl']=Config::get("fastpay_notify_url");
        $post['ext']=$info['id'];
        $post['version']=Config::get("fastpay_version");
        $post['name']=$info['holder_name'];
        $post['account']=$info['account_number'];
        $post['ifscCode']=$info['ifsc'];
        

        ksort($post);
        $sign_str = '';
        foreach ($post as $pk => $pv) {
            $sign_str .= "{$pk}={$pv}&";
        }
        $sign_str .= "key=".Config::get('fastpay_key');
//        $post_data["temp"]=$sign_str;
        
        $sign_str = strtoupper(md5($sign_str));
        
        $post['sign'] = $sign_str;


        $re = self::http_data($fastpay_daifu_url,$post,true);
        // var_dump($re);

        if (isset($re['code'])){
            if ($re['code']=='0'){
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',3); //调用支付接口开始支付，如果成功就返回1，更新status=1
                return 'success';
            }else{
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',5);//调用接口失败，这个时候就要直接是提示是接口拒绝支付
                return '调用接口失败';
                
            }
        }else
        {
            return $re['msg'];
        }



    }


public static function withdrawal_pass6($id) //sunpay
    {
        $info = Db::name('fd_withdrawal')->where('id',$id)->find();

        $sunpay_daifu_url = Config::get('sunpay_daifu_url');
        $post['mch_id'] = Config::get('sunpay_mch_id');
        $post['mch_transferId'] = $info['order_id'];
        $post['transfer_amount'] = $info['actual_money'];
        $post['apply_date']=date('Y-m-d H:i:s', $info['create_time']);
        $post['bank_code']='BDBANK';
        $post['receive_name']=$info['holder_name'];
        $post['receive_account']=$info['account_number'];
        // $post['remark']=$info['ifsc'];
        $post['back_url']=Config::get('sunpay_notify_url');
        // $post['receiver_telephone']=$info['phone_number'];


        ksort($post);
        $sign_str = '';
        foreach ($post as $pk => $pv) {
            if ($sign_str=='')
            {
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str .= "&key=".Config::get('sunpay_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtolower(md5($sign_str));
        $post['sign_type'] = 'MD5';

        $post['sign'] = $sign_str;
        // print_r($post);
        // var_dump($sunpay_daifu_url);die;

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$sunpay_daifu_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response=curl_exec($ch);


        curl_close($ch);
        // var_dump($response);

        $re=json_decode($response,true);
        
        if (isset($re['respCode'])){
            if ($re['respCode']=='SUCCESS'){

                Db::name('fd_withdrawal')->where('id',$id)->setField('status',3); //调用支付接口开始支付，如果成功就返回1，更新status=1
                return 'success';
    
            }else
            {
                // Db::name('fd_withdrawal')->where('id',$id)->setField('status',5);//调用接口失败，这个时候就要直接是提示是接口拒绝支付
                return $re['errorMsg'];
            }

            
        }else{
            return "调用sunpay失败";
        }

        


    }
//---------------代付 fastpay2 start

public static function withdrawal_pass7($id)
    {
        $info = Db::name('fd_withdrawal')->where('id',$id)->find();
//        $oceanpay_w_url = Config::get('oceanpay_w_url');
        $fastpay_daifu_url = Config::get('fastpay2_daifu_url');
        $post['mer_no'] = Config::get('fastpay2_mch_id');
        $post['order_no'] = $info['order_id'];
        $post['method'] = 'fund.apply';
        $post['order_amount'] = $info['actual_money'];
        $post['currency']='BDT';
        $post['acc_code']=$info['account_number'];
        $post['acc_name']=$info['holder_name'];
        $post['acc_no']="1234567890";//$info['phone_number'];
        $post['returnurl']= Config::get('fastpay2_notify_url');
        
       
        ksort($post);
        $sign_str = '';
         foreach ($post as $pk => $pv) {
            if ($sign_str==''){
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }
        }
        $sign_str .= Config::get('fastpay2_key');
//        $post_data["temp"]=$sign_str;
        
        $sign_str = strtolower(md5($sign_str));
        
        $post['sign'] = $sign_str;


        $re = self::http_data($fastpay_daifu_url,$post,true);
        // var_dump($re);

        if (isset($re['status'])){
            if ($re['status']=='success'){
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',3); //调用支付接口开始支付，如果成功就返回1，更新status=1
                return 'success';
            }else{
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',5);//调用接口失败，这个时候就要直接是提示是接口拒绝支付
                return '调用接口失败';
                
            }
        }else
        {
            return $re['msg'];
        }



    }
    //---------------代付 fastpay2 end 
    
    //---------------代付 lepay start

public static function withdrawal_pass8($id) //sunpay
    {
        $info = Db::name('fd_withdrawal')->where('id',$id)->find();

        $sunpay_daifu_url = Config::get('lepay_daifu_url');
        $post['mch_id'] = Config::get('lepay_mch_id');
        $post['mch_transferId'] = $info['order_id'];
        $post['transfer_amount'] = $info['actual_money'];
        $post['apply_date']=date('Y-m-d H:i:s', $info['create_time']);
        $post['bank_code']='BDT25000f012';
        $post['receive_name']=$info['holder_name'];
        $post['receive_account']=$info['account_number'];
        // $post['remark']=$info['ifsc'];
        $post['back_url']=Config::get('lepay_notify_url');
        if (isset($info['phone_number'])){        
            $post['receiver_telephone']=$info['phone_number'];
        }


        ksort($post);
        $sign_str = '';
        foreach ($post as $pk => $pv) {
            if ($sign_str=='')
            {
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str .= "&key=".Config::get('lepay_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtolower(md5($sign_str));
        $post['sign_type'] = 'MD5';

        $post['sign'] = $sign_str;
        // print_r($post);
        // var_dump($sunpay_daifu_url);die;

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$sunpay_daifu_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response=curl_exec($ch);


        curl_close($ch);
        // var_dump($response);

        $re=json_decode($response,true);
        
        if (isset($re['respCode'])){
            if ($re['respCode']=='SUCCESS'){

                Db::name('fd_withdrawal')->where('id',$id)->setField('status',3); //调用支付接口开始支付，如果成功就返回1，更新status=1
                return 'success';
    
            }else
            {
                // Db::name('fd_withdrawal')->where('id',$id)->setField('status',5);//调用接口失败，这个时候就要直接是提示是接口拒绝支付
                return $re['errorMsg'];
            }

            
        }else{
            return "调用lepay失败";
        }

        


    }
    //---------------代付 lepay end 
    
    //---------------代付 inrpay start

public static function withdrawal_pass20($id) //sunpay
    {
        $info = Db::name('fd_withdrawal')->where('id',$id)->find();

        $inrpay_daifu_url = Config::get('inrpay_url');
        $post['mchid'] = Config::get('inrpay_mch_id');
        $post['out_trade_no'] = $info['order_id'];
        $post['money'] = $info['actual_money'];
        $post['bankname']=$info['bank_name'];
        $post['subbranch']=Config::get('inrpay_notify_url');
        $post['accountname']=$info['holder_name'];
        $post['cardnumber']=$info['account_number'];
        $post['province']=$info['ifsc'];
        $post['city']='city';
        
       


        ksort($post);
        $sign_str = '';
        foreach ($post as $pk => $pv) {
            if ($sign_str=='')
            {
                $sign_str = "{$pk}={$pv}";
            }else{
                $sign_str .= "&{$pk}={$pv}";
            }

        }
        $sign_str .= "&key=".Config::get('inrpay_key');
        $sign_str_temp = $sign_str;
        $sign_str = strtoupper(md5($sign_str));
        // $post['pay_md5sign'] = 'MD5';

        $post['pay_md5sign'] = $sign_str;
        // print_r($post);
        // var_dump($sunpay_daifu_url);die;

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$inrpay_daifu_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response=curl_exec($ch);


        curl_close($ch);
        // var_dump($response);

        $re=json_decode($response,true);
        
        if (isset($re['status'])){
            if ($re['status']=='success'){

                Db::name('fd_withdrawal')->where('id',$id)->setField('status',3); //调用支付接口开始支付，如果成功就返回1，更新status=1
                return 'success';
    
            }else
            {
                // Db::name('fd_withdrawal')->where('id',$id)->setField('status',5);//调用接口失败，这个时候就要直接是提示是接口拒绝支付
                return $re['msg'];
            }

            
        }else{
            return "调用inrpay失败";
        }

        


    }
    //---------------代付 inrpay end 
    
    //---------------代付 htpay start

public static function withdrawal_pass9($id)
    {
        $info = Db::name("fd_recharge_api")->where('name','HTPAY')->find();
        if (isset($info)){
            if ($info["status"]==2){
               return "此通道已停用，请选用其他通道";
            }
        }
        
        $info = Db::name('fd_withdrawal')->where('id',$id)->find();
//        $oceanpay_w_url = Config::get('oceanpay_w_url');
        $htpay_daifu_url = Config::get('htpay_url');
        $post['merchantLogin'] = Config::get('htpay_mch_id');
        $post['orderCode'] = $info['order_id'];
        $post['currencyCode'] = 'TK';
        $post['amount'] = $info['actual_money'];
        $post['name']=$info['holder_name'];
        $post['account']=$info['account_number'];
        $post['bankCode']=$info['bank_name'];//'bkash';
        $sign_str =  $post['orderCode'].Config::get('htpay_key');
        $sign_str = md5($sign_str);
        
        $post['sign']= $sign_str;
        $post['notifyUrl']= Config::get('htpay_notify_url');
        
       
        

        $re = self::http_data($htpay_daifu_url,$post,true);
        

        if (isset($re['platformOrderCode'])){
            
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',3); //调用支付接口开始支付，如果成功就返回1，更新status=1
                return 'success';
            }else{
                
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',5);//调用接口失败，这个时候就要直接是提示是接口拒绝支付
               return '调用接口失败'.$re['detail'];
                
            }
        



    }
    //---------------代付 htpay end 
    
     //---------------代付 onepay start

public static function withdrawal_pass12($id)
    {
        // $info = Db::name("fd_recharge_api")->where('name','ONEPAY')->find();
        // if (isset($info)){
        //     if ($info["status"]==2){
        //       return "此通道已停用，请选用其他通道";
        //     }
        // }
        
        $info = Db::name('fd_withdrawal')->where('id',$id)->find();
//        $oceanpay_w_url = Config::get('oceanpay_w_url');
        
        
        if($info['bank_name']=="Easypaisa" || $info['bank_name']=="bkash"){
            $payeeType=0;
        }elseif ($info['bank_name']=="Jazzcash" || $info['bank_name']=="nagad") {
            $payeeType=2;
        }else{
            $payeeType=1;
        }
        
        $pay_daifu_url = Config::get('onepay_url');
        $post['orderNo'] = $info['order_id'];
        $post['payCode']= Config::get("onepay_pay_code");
        $post['amount'] = intval($info['actual_money']*100);
        $post['notifyUrl']=Config::get("onepay_notify_url");
        $post['payeeType']=$payeeType;
        $post['payeeName']=$info['holder_name'];
        $post['payeeFirstInfo']=$info['account_number'];
        $post['payeeSecondInfo']=$info['bank_name'];
        
        $data_temp = self::encryptionAes($post);
        $data = ["data"=>$data_temp];
        $response = self::curlPost($pay_daifu_url,$data);
        
        // var_dump($response);die;
        // $re = self::http_data($htpay_daifu_url,$post,true);
        $re =  json_decode($response, true); 

        if (isset($re['code']) && $re['code']==200){
            
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',3); //调用支付接口开始支付，如果成功就返回1，更新status=1
                return 'success';
            }else{
                
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',5);//调用接口失败，这个时候就要直接是提示是接口拒绝支付
                return '调用接口失败'.$re['message'];
                
            }
        



    }
    //---------------代付 onepay end 
    
    
    
     //---------------代付 onepay start

public static function withdrawal_pass21($id)
    {
        // $info = Db::name("fd_recharge_api")->where('name','ONEPAY')->find();
        // if (isset($info)){
        //     if ($info["status"]==2){
        //       return "此通道已停用，请选用其他通道";
        //     }
        // }
        
        $info = Db::name('fd_withdrawal')->where('id',$id)->find();
//        $oceanpay_w_url = Config::get('oceanpay_w_url');
        
        $bank_name = $info['bank_name'];
        
        if($info['bank_name']=="Easypaisa" || strtolower($info['bank_name'])=="bkash"){
            $payeeType=0;
            $bank_name = "bkash";
        }elseif (strtolower($info['bank_name'])=="jazzcash" || strtolower($info['bank_name'])=="nagad") {
            $payeeType=2;
            $bank_name = "nagad";
        }else{
            $payeeType=1;
        }
        
        $pay_daifu_url = Config::get('onepay_url');
        $post['orderNo'] = $info['order_id'];
        $post['payCode']= Config::get("onepay_pay_code");
        $post['amount'] = intval($info['actual_money']*100);
        $post['notifyUrl']=Config::get("onepay_notify_url");
        $post['payeeType']=$payeeType;
        $post['payeeName']=$info['holder_name'];
        $post['payeeFirstInfo']=$info['account_number'];
        $post['payeeSecondInfo']=$bank_name;
        if ($payeeType==1){
            $post['payeeThirdInfo']=$info['ifsc'];
            $post['payeeFourInfo']=$info['upi'];
        }
        
        $data_temp = self::encryptionAes($post);
        
        $data = ["data"=>$data_temp];
       
        $response = self::curlPost($pay_daifu_url,$data);
        
        // // $re = self::http_data($htpay_daifu_url,$post,true);
        $re =  json_decode($response, true); 

        if (isset($re['code']) && $re['code']==200){
            
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',3); //调用支付接口开始支付，如果成功就返回1，更新status=1
                return 'success';
            }else{
                
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',5);//调用接口失败，这个时候就要直接是提示是接口拒绝支付
                return '调用接口失败'.$re['message'];
                
            }
        



    }
    //---------------代付 onepay end 
    
    
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
    
    //--------------虚拟代付

public static function withdrawal_pass10($id)
    {
       
            
                Db::name('fd_withdrawal')->where('id',$id)->setField('status',6); //调用支付接口开始支付，如果成功就返回1，更新status=1
                return 'success';
            



    }
    //---------------代付 htpay end 

    public static function http_data($url, $data = NULL, $json = false)
    {
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


    public static function withdrawal_pass17($id){ //线下支付
        Db::name('fd_withdrawal')->where('id',$id)->setField('status',1); //调用支付接口开始支付，如果成功就返回1，更新status=1
                return 'success';
    }

    public static function oceanpay_w_sign($sign_data)
    {
        $hmacstr = 'accountname='.$sign_data['accountname'].'&amount='.$sign_data['amount'].'&bankname='.$sign_data['bankname'].'&cardnumber='.$sign_data['cardnumber'].'&code='.$sign_data['code'].'&email='.$sign_data['email'].'&ifsc='.$sign_data['ifsc'].'&merissuingcode='.$sign_data['merissuingcode'].'&mobile='.$sign_data['mobile'].'&notifyurl='.$sign_data['notifyurl'].'&starttime='.$sign_data['starttime'].'&key='.$sign_data['key'].'';

        $sign= strtoupper(md5($hmacstr));
        return $sign;
    }

    public static function oceanpay_w_post($url, $data)
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
        $rst = json_decode($output,true);
        return $rst;
    }

    public static function withdrawal_refuse($id)
    {
        $res = Db::name('fd_withdrawal')->where('id',$id)->setField('status',2);
        if ($res == true){
            $withdrawal_info = self::get_withdrawal_info($id);
            $refund = Db::name('fd_user')
                ->where('id',$withdrawal_info['uid'])->setInc('balance',$withdrawal_info['apply_money']);
            if ($refund == true){
                return 'success';
            }else{
                return 'error';
            }
        }else{
            return 'error_status';
        }
    }

}