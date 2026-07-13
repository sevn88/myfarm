<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 0:19
 * QQ:1467572213
 */

namespace app\index\controller;

use think\Controller;
use think\facade\Request;
use think\facade\Session;
use \app\index\model\Login as Login_m;


class Login extends Controller
{
    public function index()
    {
        if (Request::isPost()){
            $info['account'] = Request::post('account');
            $info['password'] = Request::post('password');
            $login = Login_m::login($info);
            if ($login == 'error_status') return ['code'=>0,'msg'=>lang('thisaccounthasbeenblocked1')];
            if ($login != 'error'){
                Session::set('user_id',$login['id']);
                
                return ['code'=>1,'msg'=>lang('success')];
            }else{
                return ['code'=>0,'msg'=>lang('theaccountdoesnotexist1')];
            }
        }
        return $this->fetch();
    }

    public function reg()
    {
        $lang="";
        $lang=input("param.lang");
        if ($lang){
            cookie('think_var',$lang); 
        }
        
        $lang=cookie('think_var');
        if(!$lang){
           cookie('think_var','en-us'); 
           $currentURL = "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
           return $this->redirect($currentURL);
        }
        
        $invite = Request::route(true);
        
        if ($invite){
            $this->assign('invite',$invite);
        }
        if (Request::isPost()){
            // $ip =  get_ip();
            // $check_ip = Login_m::check_ip($ip);
            // if ($check_ip == 'error') return ['code'=>0,'msg'=>'This ip has already registered an account. If you have already registered, you can try to log in or retrieve your password'];
            
             //首先验证是否有邀请码，杜绝一些问题的发生
            $invite_code = strval(Request::post('invite_code'));
            if (empty($invite_code))
            {
                return ['code'=>0,'msg'=>lang("LinkError")];
            }
            if (strlen($invite_code)!=6){
                return ['code'=>0,'msg'=>lang("LinkError")];
            }
            
            $info['username'] = Request::post('username');
            if (empty($info['username'])){
                return ['code'=>0,'msg'=>lang('entertheaccountnumber1')];
            }
            $check_username = Login_m::check_username($info['username']);
            if ($check_username == 'error') return ['code'=>0,'msg'=>lang('accountalreadyexists1')];

            $phone = Request::post('phone');
            if (empty($phone)){
                return ['code'=>0,'msg'=>''];
            }
            $check_phone = Login_m::check_phone($phone);
            if ($check_phone == 'error') return ['code'=>0,'msg'=>lang('entermobilephonenumber1')];

            $code = Request::post('code');
//            $check_code = Login_m::check_code($code);
//            if ($check_code == 'error'){
//                return ['code'=>0,'msg'=>'Verification code error'];
//            } else{
//                $info['phone'] = $check_code;
//            }
            $password = Request::post('password');
            if (!empty($password)){
                $info['password'] = md5($password);
            }else{
                return ['code'=>0,'msg'=>lang('passwordcannotbeempty1')];
            }
            $payment = Request::post('payment');
            if (!empty($payment)){
                $info['payment'] = md5($payment);
            }else{
                return ['code'=>0,'msg'=>lang('paymentpasswordcannotbeblank1')];
            }
            $info['phone'] = $phone;
            $invite_code = strval(Request::post('invite_code'));

            if (strlen($invite_code) > 0){
                $check_invite_code = Login_m::check_invite_code($invite_code);
                if ($check_invite_code==1){
                    // $info['pid'] = Login_m::get_pid_info($invite_code);
                    $pid_info = Login_m::get_pid_all_info($invite_code);
                    $info['pid']=$pid_info['id'];
                    $info['agent_id']=$pid_info['agent_id'];
                    $info['agent_username']=$pid_info['agent_username'];
                }
                elseif($check_invite_code==2){ //表示邀请码处于金字塔第5层甚至第6层了，不能用 或者邀请码不是代理或者总代的也不能用
                    return ['code'=>0,'msg'=>lang('invitationcodeisnotauth1')];
                }else{
                    return ['code'=>0,'msg'=>lang('invitecodeiswrong1')];
                }
            }else{
                // return ['code'=>0,'msg'=>'Invite code cannot be blank'];
                return ['code'=>0,'msg'=>lang('wrongregisinformation1')]; //隐藏了注册链接，但是必须通过邀请码才能注册，那就把邀请码这个隐藏起来，
//                $info['pid'] = 0; //必须有邀请码才能注册
            }

            $info['invite_code'] = Login_m::get_invite_code();
            $info['group'] = 0;
            // $balance = 
            $info['balance'] =Login_m::zhuce_zengsong('zhuce_zengsong');
            $info['total'] = 0;
            $info['financial'] = 0;
            $info['freeze'] = 0;
            $info['level'] = 0;
            $info['auto_grab'] = 0;
            $info['reg_ip'] = get_ip();
            $info['last_time'] = time();
            $info['create_time'] = time();
            $reg = Login_m::reg($info,$code);
            return $this->jsonResult($reg, lang('success'), lang('error'));
        }
        return $this->fetch();
    }
    
    //缓存
    public function dex($name){
        return $this->fetch($name);
    }

    public function sms_code()
    {
        if (Request::isPost()){
            $phone = Request::post('phone');
            $type = Request::post('type');
            $sms_code = Login_m::sms_code($phone,$type);
            if ($sms_code['code'] == 'success'){
                return ['code'=>1,'msg'=>'Sent successfully'];
            }else{
                return ['code'=>0,'msg'=>'Error'];
            }
        }
    }

    public function check_username()
    {
        if (Request::isPost()){
            $username = Request::post('username');
            $check = Login_m::check_username($username);
            return $this->jsonResult($check, lang('success'), lang('accountalreadyexists1'));
        }
    }

    public function check_phone()
    {
        if (Request::isPost()){
            $phone = Request::post('phone');
            $check = Login_m::check_phone($phone);
            return $this->jsonResult($check, lang('success'), lang('phonenumberalreadyexists1'));
        }
    }

    public function forget()
    {
        if (Request::isPost()){
            $info['phone'] = Request::post('phone');
            $info['code']= Request::post('code');
            $info['password']= Request::post('password');
            $forget = Login_m::forget($info);
            return $this->jsonResult($forget, lang('success'), lang('error'), [
                'error_code' => lang('verificationcodeerror1'),
            ]);
        }
        return $this->fetch();
    }

    public function base_to_img()
    {
        $base64 = "iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAABaklEQVQ4T62VwU7CQBCGZ7bU7aGkhDZNNBxMfAR9EA/qRR8BY+KDmBh5BE/iwQfRRzB4IBpKS2ng0AXaMUNaQhCQhJ1Lk2b2m/33n51FWBNEdBAEwSUiniPiKQAcFWnfRPRBRG++77cRcbK6HFd/FKAH0zQbUkowTROEEPO0PM9hOp2CUoq/XSK6Z/AyYwEkIhFF0aMQomnb9hy0LRg8Ho+5SMt13TtEzDl/AQzD8MkwjKbjOID4Z+Nr2UQESZJAlmUtz/NuF0CWWalUXmq12s6wsgJDh8MhzGazq/m5sgFhGH46jtP4T+amI2D5SZJ0Pc87wV6vdy2lfGap+wRLV0rdYBAE7Wq1emFZ1j48SNMURqPRKwM79Xr92DCMvYBZlsFgMPjCfr+fuq4rd3V2U1U2J4oipR+oU3Icxx39pmhvG+2Nza5pvXplG2gdDgzVPr7KnWobsMs3YPkJAIAzRDwsVPwAwPu2J+AXZ1wX1jVIDg0AAAAASUVORK5CYII=";
        $img = base64_decode($base64);
        file_put_contents('./iii/b7.png', $img);
    }
    
    
    public function changelang(){
        $lang=$_POST['lang'];
        cookie('think_var', $lang);
    }

}