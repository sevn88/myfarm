<?php
namespace app\api\controller;//注意写准确命名空间
use think\Controller;



use think\facade\Request;
use think\facade\Session;



class WithdrawController extends Controller //类名字 和控制器名字一样
{
    public function re51()
    {
        if (Request::isPost()){
            // $data = Request::post();
            
            $data =file_get_contents('php://input');
            
            $data = json_decode($data,true);

            if ($data['status']=="SUCCESS")
            {
                $info['status']=1;
            }else
            {
                $info['status']=5;
            }


            $info['order_id'] =$data['merchantOrderId'];
//            $info['p_order_id'] = $data['sysOrderId'];

            $info['err_code']=$data['errCode'];
            $info['err_msg']=$data['errMsg'];
            $info['success_time']=$data['successTime'];
            $info['money_real']=$data['amount'] / 100;
            $info['money_fee_real']=$data['fee'] / 100;
            $info['money_get_real']=$data['realAmount'] / 100;
            $model = model('Withdraw');
            $re = $model->pay51($info);
//            return json(['data'=>$re]);

            return $re;



        }
    }
    
    public function HtPay()
    {
        if (Request::isPost()){
            // $data = Request::post();
            
            $data =file_get_contents('php://input');
            
            $data = json_decode($data,true);
           
            if ($data['status']=="SUCCESS" ) 
            {
                $info['status']=1;
            }
            else{
                $info['status']=5;
            }
             
            $info['order_id'] =$data['merchantCode'];
    
            $model = model('Withdraw');
            $re = $model->htpay($info);
//            return json(['data'=>$re]);

            return $re;
        }
    }
    
    public function onepay()
    {
        if (Request::isPost()){
            // $data = Request::post();
            
            $data =file_get_contents('php://input');
            
            $data_temp = json_decode($data,true);
            $data=$data_temp["data"];
            $data_temp = self::decryptAes($data);
            $data = $data_temp;
           
            if ($data['status']==2 ) 
            {
                $info['status']=1;
            }
            else{
                $info['status']=5;
            }
             
            $info['order_id'] =$data['merchantNo'];
    
            $model = model('Withdraw');
            $re = $model->htpay($info);
//            return json(['data'=>$re]);

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

            if ($data['tradeResult']=="1")
            {
                $info['status']=1;
            }else
            {
                $info['status']=5;
            }


            $info['order_id'] =$data['merTransferId'];
            $info['respCode']=$data['respCode'];
            $info['money_real']=$data['transferAmount'];
            $info['success_time']=$data['applyDate'];

            $model = model('Withdraw');
            $re = $model->dzxum($info);
//            return json(['data'=>$re]);

            return $re;



        }
    }
    public function FastPay()
    {
        if (Request::isPost()){
            $data = Request::post();

            if ($data['status']=="1")
            {
                $info['status']=1;
            }else
            {
                $info['status']=5;
            }


            $info['order_id'] =$data['orderNo'];
           
            $info['money_real']=$data['amount'];
            // $info['success_time']=$data['applyDate'];

            $model = model('Withdraw');
            $re = $model->fastpay($info);
//            return json(['data'=>$re]);

            return $re;



        }
    }
    public function FastPay2()
    {
        if (Request::isPost()){
            $data = Request::post();

            if ($data['result']=="success")
            {
                $info['status']=1;
            }elseif ($data['result']=='fail')
            {
                $info['status']=3;
            }else{
                $info['status']=5;
            }


            $info['order_id'] =$data['order_no'];
           
            $info['money_real']=$data['order_amount'];
            // $info['success_time']=$data['applyDate'];

            $model = model('Withdraw');
            $re = $model->fastpay2($info);
//            return json(['data'=>$re]);

            return $re;



        }
    }
     public function sunpay()
    {
        if (Request::isPost()){
            $data = Request::post();

            if ($data['tradeResult']=="1")
            {
                $info['status']=1;
            }else
            {
                $info['status']=5;
            }


            $info['order_id'] =$data['merTransferId'];
            $info['respCode']=$data['respCode'];
            $info['money_real']=$data['transferAmount'];
            $info['success_time']=$data['applyDate'];

            $model = model('Withdraw');
            $re = $model->sunpay($info);
//            return json(['data'=>$re]);

            return $re;



        }
    }
    
    public function inrpay()
    {
        if (Request::isPost()){
            $data = Request::post();

            if ($data['status']=="processed")
            {
                $info['status']=1;
            }else
            {
                $info['status']=5;
            }


            $info['order_id'] =$data['orderId'];
            // $info['respCode']=$data['respCode'];
            $info['money_real']=$data['amount'];
            $info['success_time']=date('Y-m-d H:i:s') ;

            $model = model('Withdraw');
            $re = $model->htpay($info);
//            return json(['data'=>$re]);

            return $re;



        }
    }
}