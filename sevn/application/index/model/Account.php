<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 4:28
 * QQ:1467572213
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Account extends Model
{
    public static function card($user_id, $info)
    {
        
        
        //先检测这个人的卡是否已经绑定其他的账号了，如果绑定提示错误
        if (isset($info['account_number'])){
            $card = Db::name('fd_user')->where('account_number',$info['account_number'])->select();
            if (count($card)>=1){
                return "error1";
            }    
        }
        //先检测这个人的地址是否已经绑定其他的账号了，如果绑定提示错误
        if (isset($info['address_usdt'])){
            $card = Db::name('fd_user')->where('address_usdt',$info['address_usdt'])->select();
            if (count($card)>=1){
                return "error1";
            }    
        }
        
        //再去检测这个人绑定的时候，这个人注册的ip是不是有其他的用户在用，如果有，提示错误
        $user_info = Db::name('fd_user')->where('id',$user_id)->find();
       if (!is_null($user_info['reg_ip']) && isset($user_info['reg_ip']) && $user_info['reg_ip']!=''){
            $card = Db::name('fd_user')->where("reg_ip",$user_info['reg_ip'])->select();
            if (count($card)>=2){
                return "error2";
            }
       }
        
        $card = Db::name('fd_user')->where('id', $user_id)->update($info);
        if ($card == true) {
            return 'success';
        } else {
            return 'error';
        }
    }

    public static function invitation($user_id,$qr_code)
    {
        $invitation = Db::name('fd_user')->where('id',$user_id)->setField('qrcode',$qr_code);
        if ($invitation == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function address($user_id,$info)
    {
        $address = Db::name('fd_user')->where('id',$user_id)->update($info);
        if ($address == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function set_password($user_id,$type,$code,$password)
    {
        $where['code'] = $code;
        $where['type'] = $type;
        $where['status'] = 0;
        $check_code = Db::name('fd_sms_code')->where($where)->find();
        if ($check_code){
            switch ($type)
            {
                case 2:
                    $field = 'password';
                    break;
                case 3:
                    $field = 'payment';
                    break;
            }
            $set_pass = Db::name('fd_user')->where('id',$user_id)->setField($field,md5($password));
            if ($set_pass == true){
                Db::name('fd_sms_code')->where($where)->setField('status',1);
                return 'success';
            }else{
                return 'error';
            }
        }else{
            return 'error';
        }
    }

    public static function sms_code($phone,$type)
    {
        $account = Config::get('sms_code_account');
        $key = Config::get('sms_code_key');
        $zone = Config::get('sms_code_zone');
        $datetime = date('YmdHis');
        $sign = md5($account.$key.$datetime);
        $code = \app\index\model\StringCode::randString(4,1);
        $url = "http://sms.skylinelabs.cc:20003/sendsmsV2?account=$account&sign=$sign&datetime=$datetime";
        $data = [
            'content' => 'Your SMS verification code is:'.$code,
            'numbers' => $zone.$phone,
        ];
        $send_code = self::send_post($url, $data);
        if ($send_code['status'] == 0){
            $sms_code['phone'] = $phone;
            $sms_code['code'] = $code;
            $sms_code['type'] = $type;
            $sms_code['status'] = 0;
            $sms_code['create_time'] = time();
            $add_sms_code = Db::name('fd_sms_code')->insert($sms_code);
            if ($add_sms_code == true){
                return 'success';
            }else{
                return 'error';
            }
        }else{
            return 'error';
        }
    }

    //生成用户二维码
    public static function create_qrcode($invite_code,$user_id)
    {
        $dir = './qrcode/user/' . $user_id . '.png';
        if(file_exists($dir)) {
            return $dir;
        }
        $qrCode = new \Endroid\QrCode\QrCode(SITE_URL . url('@index/user/register/invite_code/'.$invite_code));
        //设置前景色
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' =>0, 'a' => 0]);
        //设置背景色
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        //设置二维码大小
        $qrCode->setSize(230);
        $qrCode->setPadding(5);
        $qrCode->setLogoSize(40);
        $qrCode->setLabelFontSize(14);
        $qrCode->setLabelHalign(100);

        $dir = './qrcode/user/';
        if(!file_exists($dir)) {
            mkdir($dir, 0777,true);
        }
        $qrCode->save($dir . '/' . $user_id . '.png');
        $qr = \Env::get('root_path').'public/qrcode/user/'. $user_id . '.png';
//        $bgimg1 = \Env::get('root_path').'public/public/img/hb.jpg';
//
//        $image = \think\Image::open($bgimg1);
//        $image->water($qr,[255,743])->save(\Env::get('root_path').'public/upload/qrcode/user/'.$n.'/'.$user_id.'-1.png');
        $url = '/qrcode/user/'.$user_id.'.png';
        return $url;
    }

    public static function send_post($url, $data)
    {
        header("Content-type:application/json;charset=utf-8");
        $data = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type：application/json;charset=UTF-8",
                "Content-Length: " . strlen($data))
        );
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        $return = json_decode($return_content, true);
        return $return;
    }

    public static function freeze($user_id)
    {
        $where['uid'] = $user_id;
        $where['status'] = 2;
        $list = Db::name('fd_order')->where($where)->select();
        return $list;
    }

    public static function team_data($user_id)
    {
        $start_time = mktime(0, 0, 0);
        $end_time = strtotime('+1 day', $start_time);
        $today_commission = [
            ['aid', '=', $user_id],
            ['status', '=', 1],
            ['create_time', '>', $start_time],
            ['create_time', '<', $end_time],
        ];
        $info['today_commission'] = Db::name('fd_order')->where($today_commission)->sum('order_earnings');

        $total_commission = [
            ['aid', '=', $user_id],
            ['status', '=', 1],
        ];
        $info['total_commission'] = Db::name('fd_order')->where($total_commission)->sum('order_earnings');

        $num = [
            ['aid', '=', $user_id],
            ['status', '=', 1],
        ];

        $info['num'] = Db::name('fd_order')->where($num)->count();

        $all_order = Db::name('fd_order')->where('aid',$user_id)->count();
        $over_order = Db::name('fd_order')->where('status',1)->where('aid',$user_id)->count();
        if ($all_order > 0 && $over_order > 0){
            $info['rate'] = round($all_order / $over_order,2);
        }else{
            $info['rate'] = 0;
        }
        $today = [
            ['pid', '=', $user_id],
            ['status', '=', 1],
            ['create_time', '>', $start_time],
            ['create_time', '<', $end_time],
        ];
        $info['today_recharge'] = Db::name('fd_recharge')->where($today)->sum('money');

        $info['today_withdraw'] = Db::name('fd_withdrawal')->where($today)->sum('actual_money');

        return $info;
    }

    public static function team_user($user_id)
    {
        $team_user = Db::name('fd_user')->where('pid',$user_id)->select();
        return $team_user;
    }

    public static function level_info()
    {
        $level_info = Db::name('fd_level')->where('status',1)->select();
        return $level_info;
    }

    public static function set_pass($user_info,$o_pass,$c_pass)
    {
        if ($user_info['password'] == md5($o_pass)){
            $data['password'] = md5($c_pass);
            $data['update_time'] = time();
            $set = Db::name('fd_user')->where('id',$user_info['id'])->update($data);
            if ($set == true){
                return 'success';
            }else{
                return 'error';
            }
        }else{
            return 'error_old';
        }
    }

    public static function set_pass_pay($user_info,$o_pass,$c_pass)
    {
        if ($user_info['payment'] == md5($o_pass)){
            $data['payment'] = md5($c_pass);
            $data['update_time'] = time();
            $set = Db::name('fd_user')->where('id',$user_info['id'])->update($data);
            if ($set == true){
                return 'success';
            }else{
                return 'error';
            }
        }else{
            return 'error_old';
        }
    }
    
    public static function get_cs($user_id){
        
        $notfinded = true;
        $info =[];
        $pid=0;
        $uinfo = Db::name("fd_user")->where('id',$user_id)->find();
        
        $pid = $uinfo['pid']; //第一级
        // var_dump($pid);
        if ($pid>0){
            $info_cs = Db::name('fd_cs')->where('uid',$pid)->find();
            if ($info_cs){
                $info = $info_cs;
                $notfinded=false;
            }else{
                $uinfo = Db::name("fd_user")->where('id',$pid)->find();    
                $pid = $uinfo['pid']; //第二级
                // var_dump($pid);
                if ($pid>0){
                    $info_cs = Db::name('fd_cs')->where('uid',$pid)->find();
                    if ($info_cs){
                        $info = $info_cs;
                        $notfinded=false;
                    }else{
                        $uinfo = Db::name("fd_user")->where('id',$pid)->find();    
                        $pid = $uinfo['pid']; //第三级
                        // var_dump($pid);
                        if ($pid>0){
                            $info_cs = Db::name('fd_cs')->where('uid',$pid)->find();
                            if ($info_cs){
                                $info = $info_cs;
                                $notfinded=false;
                            }else{
                                $uinfo= Db::name("fd_user")->where('id',$pid)->find();
                                $pid=$uinfo['pid'];
                                // var_dump($pid);
                                if($pid>0){
                                    $info_cs = Db::name('fd_cs')->where('uid',$pid)->find();
                                    if($info_cs){
                                        $info = $info_cs;
                                        $notfinded=false;
                                    }
                                }
                            }
                        }
                    }
                    
                }
            }
            
        }
        if ($notfinded){
            $info = Db::name("system_cs")->where('id',2)->find();    
        }
        
        // var_dump($info);die;
        
        return $info;
    }

}