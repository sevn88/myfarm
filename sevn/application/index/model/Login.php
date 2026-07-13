<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/22
 * Time: 0:19
 * QQ:1467572213
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;
use \app\index\model\Base;

class Login extends Model
{
    public static function login($info)
    {
        $where = [
            ['username', 'like', $info['account']],
            ['phone', 'like', $info['account']],
        ];
        $check_info = Db::name('fd_user')->whereOr($where)->find();
        if ($check_info['status'] == 2) return 'error_status';
        if ($check_info['status'] == 1) {
            if ($check_info['password'] == md5($info['password'])) {
                $session_id = session_id();
                Db::name('fd_user')->where('id', $check_info['id'])->setField('last_ip', get_ip());
                Db::name('fd_user')->where('id', $check_info['id'])->setField('last_time', time());
                Db::name('fd_user')->where('id', $check_info['id'])->setField('session_id', $session_id);
                return $check_info;
            } else {
                return 'error';
            }
        } else {
            return 'error';
        }
    }

    public static function zhuce_zengsong(){
        return (float)Base::get_config('zhuce_zengsong');
    }
    public static function reg($info, $code)
    {
        
        $is_two = false;
        $reg = false;
        
        // $reg = Db::name('fd_user')->insert($info);
        $insert_id = Db::name('fd_user')->insertGetId($info);
        if ($insert_id>0){
            $reg=true;
            // 分表已移除，用户数据只存在 fd_user 主表中
        }
        
        
        if ($reg == true) {
            return 'success';
        } else {
            return 'error';
        }
        die;

        $where['code'] = $code;
        $where['type'] = 1;
        $where['status'] = 0;
        $check_code = Db::name('fd_sms_code')->where($where)->find();
        if ($check_code) {
            $reg = Db::name('fd_user')->insert($info);
            if ($reg == true) {
                Db::name('fd_sms_code')->where($where)->setField('status', 1);
                return 'success';
            } else {
                return 'error';
            }
        } else {
            return 'error';
        }
    }

    public static function forget($info)
    {
        $where['phone'] = $info['phone'];
        $where['code'] = $info['code'];
        $where['type'] = 4;
        $where['status'] = 0;
        $check_code = Db::name('fd_sms_code')->where($where)->find();
        if ($check_code) {
            $edit_password = Db::name('fd_user')
                ->where('phone', $check_code['phone'])
                ->setField('password', md5($info['password']));
            if ($edit_password == true) {
                Db::name('fd_sms_code')->where($where)->setField('status', 1);
                return 'success';
            } else {
                return 'error';
            }
        } else {
            return 'error_code';
        }

    }

    public static function check_username($username)
    {
        $check = Db::name('fd_user')->where('username', $username)->count();
        if ($check == 0) {
            return 'success';
        } else {
            return 'error';
        }
    }
    public static function check_ip($ip){
        $ip_count = Db::name("fd_user")->where("reg_ip",$ip)->count();
        if ($ip_count==0){
            return "ok";
        }else{
            return "error";
        }
    }

    public static function check_phone($phone)
    {
        $check = Db::name('fd_user')->where('phone', $phone)->count();
        if ($check == 0) {
            return 'success';
        } else {
            return 'error';
        }
    }

    public static function sms_code($phone, $type)
    {
        $account = Config::get('sms_code_account');
        $key = Config::get('sms_code_key');
        $zone = Config::get('sms_code_zone');
        $datetime = date('YmdHis');
        $sign = md5($account . $key . $datetime);
        $code = \app\index\model\StringCode::randString(4, 1);
        $url = "http://sms.skylinelabs.cc:20003/sendsmsV2?account=$account&sign=$sign&datetime=$datetime";
        if ($type == 4) {
            $data = [
                'content' => '[EARNHUB] your Mobile Registration Code is ' . $code . ' Valid for 5 Minutes',
                'numbers' => $zone . $phone,
            ];
        } else {
            $data = [
                'content' => 'Your SMS verification code is:' . $code,
                'numbers' => $zone . $phone,
            ];
        }

        $send_code = self::send_post($url, $data);
        $send_code['status'] = 0;
        if ($send_code['status'] == 0) {
            $sms_code['phone'] = $phone;
            $sms_code['code'] = $code;
            $sms_code['type'] = $type;
            $sms_code['status'] = 0;
            $sms_code['create_time'] = time();
            $add_sms_code = Db::name('fd_sms_code')->insert($sms_code);
            if ($add_sms_code == true) {
                return ['code' => 'success'];
            } else {
                return ['code' => 'error'];
            }
        } else {
            return ['code' => 'error'];
        }
    }

    public static function check_code($code)
    {
        $where['code'] = $code;
        $where['type'] = 1;
        $where['status'] = 0;
        $check = Db::name('fd_sms_code')->where($where)->find();
        if ($check['status'] == 0) {
            return $check['phone'];
        } else {
            return 'error';
        }
    }

    public static function get_pid_info($invite_code)
    {
        $pid_info = Db::name('fd_user')->where('invite_code', $invite_code)->value('id');
        if ($pid_info) {
            return $pid_info;
        } else {
            return 0;
        }
    }
    
    public static function get_pid_all_info($invite_code)
    {
        $pid_info = Db::name('fd_user')->where('invite_code', $invite_code)->find();
        if ($pid_info) {
            return $pid_info;
        } else {
            return 0;
        }
    }

    public static function get_invite_code()
    {
        // $invite_code = \app\index\model\StringCode::randString(6, 1);//这个地方为了增加更多的邀请码的可能性，把这里的限制打开，可以从所有的字母与数字当中提取。
        $invite_code = \app\index\model\StringCode::randString(6);
        $check_invite_code = Db::name('fd_user')->where('invite_code', $invite_code)->count();
        if ($check_invite_code > 0) {
            self::get_invite_code();
        } else {
            return $invite_code;
        }
    }

    public static function check_invite_code($invite_code)
    {
        //这个地方修改。只允许这个注册邀请码上面还有3个父辈。也就是说这个邀请码属于第一层到第四层，不能是第五层
        if ($invite_code) {
            $invite_code_can_use = true;
            
            $info=Db::name("fd_user")->where("invite_code",$invite_code)->find();
            if (intval($info['role'])==1){
                //说明这个只是普通的用户，而不是代理或者总代，不能用
                return 2;
            }
            
            $pid = $info['pid'];
            if (isset($pid) && $pid>0)//说明他上面还有 3
            {
                $pid = Db::name("fd_user")->where("id",$pid)->value("pid");
                if (isset($pid) && $pid>0)//说明他上面还有 2
                {
                    $pid = Db::name("fd_user")->where("id",$pid)->value("pid");
                    if (isset($pid) && $pid>0)//说明他上面还有 1
                    {
                        $pid = Db::name("fd_user")->where("id",$pid)->value("pid");
                        if (isset($pid) && $pid>0)//说明他上面还有 1
                        {
                            $invite_code_can_use = false;
                            return 2; //说明上面还有代理，提供的邀请码属于第5层，不能是这样的
                        }
                    }
                    
                }    
            }
            $check_invite_code = Db::name('fd_user')->where('invite_code', $invite_code)->count();
            if ($check_invite_code > 0) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
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

}