<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 4:28
 * QQ:1467572213
 */

namespace app\index\controller;

use think\facade\Request;
use think\facade\Session;
use \app\index\model\Account as Account_m;


class Account extends Base
{
    public function index()
    {
        $http = Request::domain();
        $invite_url = $http.'/index/login/reg/invite_code/';
        $this->assign('invite_url',$invite_url);
        return $this->fetch();
    }

    public function aboutus()
    {
        return $this->fetch();
    }

    public function out()
    {
        Session::delete('user_id');
        return ['code'=>1,'msg'=>lang("HasExitedSafely")];
    }

    public function personal()
    {
        return $this->fetch();
    }

    public function card()
    {
        $user_id = Session::get('user_id');
        if (Request::isPost()){
            $info['bank_name'] = Request::post('bank_name');
            if (!$info['bank_name']) return ['code'=>0,'msg'=>lang("BankNameCannotBlank")];
            $info['holder_name'] = Request::post('holder_name');

            if (!$info['holder_name'])return ['code'=>0,'msg'=>lang("HolderNameCannotBlank")];

            $info['account_number'] = Request::post('account_number');
            if (!$info['account_number']) return ['code'=>0,'msg'=>lang("AccountNumberCannotEmpty")];
            
            $info['ifsc']=Request::post("ifsc");
            $info['upi']=Request::post("upi");
            if ($info['bank_name']=='bankcard'){
                if (!$info['upi'] || !$info['ifsc']){
                    return ['code'=>0,'msg'=>lang("bankinfo")];
                }
            }

            // $info['phone_number'] = Request::post('phone_number');
            // if (!$info['phone_number']) return ['code'=>0,'msg'=>'Phone number cannot be empty'];

            // $info['email'] = Request::post('email');
            // if (!$info['email']) return ['code'=>0,'msg'=>'Mailbox cannot be empty'];

            // $info['ifsc'] = Request::post('ifsc');
            // if (!$info['ifsc']) return ['code'=>0,'msg'=>'Account opening cannot be empty'];

            // $info['upi'] = Request::post('upi');
            // if (!$info['upi'] ) return ['code'=>0,'msg'=>'UPI cannot be empty'];
           
           
            $info['update_time'] = time();
           
            $card = Account_m::card($user_id,$info);
            return $this->jsonResult($card, lang('success'), lang('error'), [
                'error1' => lang("ThisCardBindingNotSupported"),
                'error2' => lang("AnErrorOccurredWhileBindingInformation"),
            ]);
        }
        return $this->fetch();
    }
    
    
     public function usdt(){

        $user_id = Session::get('user_id');
        if (Request::isPost()){
            $info['type'] = Request::post('type');
            if (!$info['type']) return ['code'=>0,'msg'=>lang("USDTCannotEmpty")];
            
            $info['address_usdt'] = Request::post('address_usdt');
            if (!$info['address_usdt']) return ['code'=>0,'msg'=>lang("USDTAddressCannotEmpty")];
            
            $info['phone_number'] = Request::post('phone_number');
            if (!$info['phone_number']) return ['code'=>0,'msg'=>lang("mobilecannotbeempty1")];

            $info['email'] = Request::post('email');
            if (!$info['email']) return ['code'=>0,'msg'=>lang("EmailCannotEmpty")];

            // var_dump($card);die;
            $card = Account_m::card($user_id,$info);
            return $this->jsonResult($card, lang('success'), lang('error'));
        }
        return $this->fetch();
    }

    public function invitation()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        if (empty($user_info['qrcode'])){
            // $qr_code = Account_m::create_qrcode($user_info['invite_code'],$user_id);
            $qr_code="";
            $add_qr_code = Account_m::invitation($user_id,$qr_code);
            if ($add_qr_code == 'success'){
                $this->assign('qr_code',$qr_code);
            }
        }else{
            $this->assign('qr_code',$user_info['qrcode']);
        }
        $http = Request::domain();
        $invite_url = $http.'/index/login/reg/invite_code/';
        $this->assign('invite_url',$invite_url);
        return $this->fetch();
    }

    public function message()
    {
        return $this->fetch();
    }

    public function address()
    {
        $user_id = Session::get('user_id');
        if (Request::isPost()){
            $info['address_name'] = Request::post('address_name');
            if (!$info['address_name']) return ['code'=>0,'msg'=>lang("AddressNameCannotEmpty")];

            $info['address_phone'] = Request::post('address_phone');
            if (!$info['address_phone']) return ['code'=>0,'msg'=>lang("PhoneAddressCannotEmpty")];

            $info['address_info'] = Request::post('address_info');
            if (!$info['address_info']) return ['code'=>0,'msg'=>lang("AddressInformationCannotEmpty")];
            $info['update_time'] = time();
            $address = Account_m::address($user_id,$info);
            return $this->jsonResult($address, lang('success'), lang('error'));
        }
        return $this->fetch();
    }

    public function invite_task()
    {
        return $this->fetch();
    }

    public function safe()
    {
        return $this->fetch();
    }

    public function password()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        if (Request::isPost()){
            $o_pass = Request::post('o_pass');
            $n_pass = Request::post('n_pass');
            $c_pass = Request::post('c_pass');
            if ($n_pass != $c_pass){
                return ['code'=>0,'msg'=>lang("TwoPasswordsAreInconsistent")];
            }else{
                $set_password = Account_m::set_pass($user_info,$o_pass,$c_pass);
                return $this->jsonResult($set_password, lang('success'), lang('error'), [
                    'error_old' => lang('OriginalPasswordError'),
                ]);
            }
        }
        return $this->fetch();
    }

    public function password_pay()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        if (Request::isPost()){
            $o_pass = Request::post('o_pass');
            $n_pass = Request::post('n_pass');
            $c_pass = Request::post('c_pass');
            if ($n_pass != $c_pass){
                return ['code'=>0,'msg'=>lang("TwoPasswordsAreInconsistent")];
            }else{
                $set_password = Account_m::set_pass_pay($user_info,$o_pass,$c_pass);
                return $this->jsonResult($set_password, lang('success'), lang('error'), [
                    'error_old' => lang('OriginalPasswordError'),
                ]);
            }
        }
        return $this->fetch();
    }

    public function sms_code()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        if (Request::isPost()){
            $type = Request::post('type');
            $send_sms = Account_m::sms_code($user_info['phone'],$type);
            return $this->jsonResult($send_sms, 'Sent successfully', 'Error');
        }
    }

    public function freeze()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        $freeze = Account_m::freeze($user_id);
        $this->assign('list',$freeze);
        return $this->fetch();
    }

    public function team()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\Base::get_user_info($user_id);
        $this->assign('user_info',$user_info);

        $team_data = Account_m::team_data($user_id);
        $this->assign('team_data',$team_data);

        $team_user = Account_m::team_user($user_id);
        $this->assign('team_user',$team_user);

        $level_info = Account_m::level_info();
        $this->assign('level_info',$level_info);
        return $this->fetch();
    }
    
    public function contact(){
        $http = Request::domain();
        $invite_url = $http.'/index/login/reg/invite_code/';
        $this->assign('invite_url',$invite_url);
        $user_id = Session::get('user_id');
        $info = Account_m::get_cs($user_id);
        // var_dump($info);die;
        $this->assign("s_time",$info['btime']);
        $this->assign("e_time",$info['etime']);
        $this->assign("link",$info['link']);
        $this->assign("WhatsappTelegram",$info['WhatsappTelegram']);
        return $this->fetch();
    }

}