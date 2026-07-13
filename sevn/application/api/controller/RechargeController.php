<?php
namespace app\api\controller;//注意写准确命名空间
use think\Controller;



use think\facade\Request;
use think\facade\Session;




class RechargeController extends Controller //类名字 和控制器名字一样
{
    public function re51()
    {
        if (Request::isPost()){
            $data = Request::post();

            if ($data['status']=="SUCCESS") $info['status']=1;
            if ($data['status']=="FAIL")    $info['status']=2;
            if ($data['status']=="NOTPAY")  $info['status']=3;
            if ($data['status']=="CLOSED")  $info['status']=2;

            $info['order_id'] =$data['merchantOrderId'];
//            $info['p_order_id'] = $data['sysOrderId'];

            $info['err_code']=$data['errCode'];
            $info['err_msg']=$data['errMsg'];
            $info['success_time']=$data['successTime'];
            $info['money_real']=$data['amount'] / 100;
            $info['money_fee_real']=$data['fee'] / 100;
            $info['money_get_real']=$data['realAmount'] / 100;
            $model = model('Recharge');
            $re = $model->pay51($info);


            return $re;

        }
    }
    public function HtPay0()
    {
        if (Request::isPost()){
            // $data =file_get_contents('php://input');
            // $data = json_decode($data);
            $data = Request::post();
            // return "zzz";

            if ($data['status']=="SUCCESS")
            {
                $info['status']=1;
                
            }
            else{
                $info['status']=2;
            }
            
            $info['order_id'] =$data['merchantCode'];
            $info['money_real']=$data['paidAmount'];
        
            $model = model('Recharge');
            $re = $model->htpay($info);


            return $re;

        }
    }
    
    public function HtPay()
    {
        if (Request::isPost()){
            // $data = Request::post();
            $data = file_get_contents('php://input');
            $data = json_decode($data,true);
            // var_dump($data);die;

            if ($data['status']=="SUCCESS")
            {
                $info['status']=1;
                
            }
            else{
                $info['status']=2;
            }
            
            $info['order_id'] =$data['merchantCode'];
            $info['money_real']=$data['paidAmount'];
        
            $model = model('Recharge');
            $re = $model->htpay($info);


            return $re;

        }
    }
    // public function HtPay()
    // {
    //     if (Request::isPost()){
           
    //         $data =file_get_contents('php://input');
            
    //         $data_temp = json_decode($data,true);
    //         // $data= $data_temp["data"];
    //         // $data_temp = self::decryptAes1($data);
    //         // print_r($data);
    //         // echo $data['status'];
    //         // die;
    //         // var_dump($data_temp);die;
    //         // $data = json_decode($data_temp,true);
    //         $data = $data_temp;

    //         if ($data['status']==2 ) 
    //         {
    //             $info['status']=1;
    //         }
    //         else{
    //             $info['status']=2;
    //         }
             
           

    //         $info['order_id'] =$data['merchantNo'];
    //         $info['money_real']=$data['amount']/100 ;
            
    //         $model = model('Recharge');
    //         $re = $model->onpay($info);


    //         return $re;

    //     }
    // }
    
    
     public function onepay()
    {
        if (Request::isPost()){
           
            $data =file_get_contents('php://input');
            
            
            $data_temp = json_decode($data,true);
        //   print_r($data_temp);die;
            $data= $data_temp["data"];
            $data_temp = self::decryptAes($data);
            // print_r($data);
            // echo $data['status'];
            // die;
            // var_dump($data_temp);die;
            // $data = json_decode($data_temp,true);
            $data = $data_temp;

            if ($data['status']==2 ) 
            {
                $info['status']=1;
            }
            else{
                $info['status']=2;
            }
             
           

            $info['order_id'] =$data['merchantNo'];
            $info['money_real']=$data['amount']/100 ;
            $info['money']=$info['money_real'];
            $model = model('Recharge');
            $re = $model->onepay($info);


            return $re;

        }
    }
    
     public function mpay()
   {
        if (Request::isPost()){
           
            $data =file_get_contents('php://input');
            
            $data = json_decode($data,true);
            // print_r($data);
            // echo $data['status'];
            // die;

            if ($data['status']=="1" ) $info['status']=1;
            if ($data['status']=="3")    $info['status']=2;
            if ($data['status']=="2")  $info['status']=3;
           

            $info['order_id'] =$data['orderNo'];
//            $info['p_order_id'] = $data['sysOrderId'];
            
            // $info['money_real']=$data['realAmount'] ;
            $info['money_real']=$data['amount'] ;
            
            $model = model('Recharge');
            $re = $model->onepay($info);


            return $re;

        }
    }
    
     public function onepay2()
    {
        
        if (Request::isPost()){
           
            $data =file_get_contents('php://input');
       
            $data_temp = json_decode($data,true);
            $data= $data_temp;
            // print_r($data_temp);die;
            if ($data['status']==2 ) 
            {
                $info['status']=1;
            }
            else{
                $info['status']=2;
            }
             
           

            $info['order_id'] =$data['merchantNo'];
            $info['money_real']=$data['amount']/100 ;
            $info['money']=$info['money_real'];
            
            $model = model('Recharge');
            $re = $model->onepay($info);


            return $re;

        }
    }
    /**解密
     * @param $aesSecret
     * @return false|string
     */
    public function decryptAes($aesSecret)
    {
        $str="";
        for($i=0;$i<strlen($aesSecret)-1;$i+=2){
            $str.=chr(hexdec($aesSecret[$i].$aesSecret[$i+1]));
        }
        $jsonData =  openssl_decrypt($str,'AES-128-CBC','yE5ix5Zz3U2gI27G', OPENSSL_RAW_DATA,'yE5ix5Zz3U2gI27G');
        $data = json_decode($jsonData,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        return $data;
    }
    
    public function dzxum()
    {
        if (Request::isPost()){
            $data = Request::post();

            if ($data['tradeResult']=="1"){
                $info['status']=1;
            } else
            {
                $info['status']=2;
            }
//            if ($data['status']=="FAIL")    $info['status']=2;
//            if ($data['status']=="NOTPAY")  $info['status']=3;
//            if ($data['status']=="CLOSED")  $info['status']=2;

            $info['order_id'] =$data['mchOrderNo'];
//            $info['p_order_id'] = $data['sysOrderId'];
//
//            $info['err_code']=$data['errCode'];
//            $info['err_msg']=$data['errMsg'];
            $info['success_time']=$data['orderDate'];
            $info['money_real']=$data['amount'];
            
            $model = model('Recharge');
            $re = $model->dzxum($info);


            return $re;

        }
    }
    
  
     public function FastPay()
    {
        if (Request::isPost()){
           
            $data =file_get_contents('php://input');
            
            $data = json_decode($data,true);
            // print_r($data);
            // echo $data['status'];
            // die;

            if ($data['status']=="1" ) $info['status']=1;
            if ($data['status']=="3")    $info['status']=2;
            if ($data['status']=="2")  $info['status']=3;
           

            $info['order_id'] =$data['orderNo'];
//            $info['p_order_id'] = $data['sysOrderId'];
            
            // $info['money_real']=$data['realAmount'] ;
            $info['money_real']=$data['amount'] ;
            
            $model = model('Recharge');
            $re = $model->fastpay($info);


            return $re;

        }
    }
    public function FastPay2()
    {
        if (Request::isPost()){
           
            $data =file_get_contents('php://input');
            
            $data = json_decode($data,true);

            if ($data['status']=="success" ) $info['status']=1;
            if ($data['status']=="fail")    $info['status']=2;
            if ($data['status']=="waiting")  $info['status']=3;
           

            $info['order_id'] =$data['order_no'];
//            $info['p_order_id'] = $data['sysOrderId'];
            
            // $info['money_real']=$data['realAmount'] ;
            $info['money_real']=$data['order_amount'] ;
            
            $model = model('Recharge');
            $re = $model->fastpay2($info);
        return $re;
        }
    }
     public function sunpay()
    {
        if (Request::isPost()){
            $data = Request::post();

            if ($data['tradeResult']=="1"){
                $info['status']=1;
            } else
            {
                $info['status']=2;
            }

            $info['order_id'] =$data['mchOrderNo'];

            $info['success_time']=$data['orderDate'];
            $info['money_real']=$data['amount'];
            
            $model = model('Recharge');
            $re = $model->sunpay($info);


            return $re;

        }
    }
    
     public function inrpay()
    {
        if (Request::isPost()){
           
            // $data =file_get_contents('php://input');
            $data = Request::post();
            // // $data = json_decode($data,true);
            // print_r($data);
            // echo $data['returncode'];
            // die;

            if ($data['returncode']=="00" ){
              $info['status']=1;  
            } else{
                $info['status']=2;
            }
           

            $info['order_id'] =$data['orderid'];
//            $info['p_order_id'] = $data['sysOrderId'];
            
            // $info['money_real']=$data['realAmount'] ;
            $info['money_real']=$data['amount'] ;
            
            $model = model('Recharge');
            $re = $model->inrpay($info);


            return $re;

        }
    }
}