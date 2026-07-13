<?php

namespace app\admin\model;

use think\Model;
use think\Db;

class Users extends Model
{
    public static function get_user_info($id)
    {
        $user_info = Db::name('fd_user')->where('id', $id)->find();
        return $user_info;
    }
    public static function customer_tree(){
        
    }
    /**
     * 编辑用户
     */
    public static function edit_users($id, $edit_info)
    {
        //判断是否修改了rate，如果修改了，那就要更新所有层级的该下属的rate
        
        $user_info = Db::name('fd_user')->where('id',$id)->find();
        // print_r($user_info);
        // print_r($edit_info);
        if ($user_info['agent_rate']!=$edit_info['agent_rate'] && $id==$user_info['agent_id']){
            Db::name('fd_user')->where('agent_id', (int)$id)->update(['agent_rate' => (int)$edit_info['agent_rate']]);
        }
        if ($user_info['agent_rate']!=$edit_info['agent_rate'] && intval($edit_info['role'])==3){
            Db::name('fd_user')->where('pid', (int)$id)->update(['agent_rate' => (int)$edit_info['agent_rate']]);
        }

        $edit_users = Db::name('fd_user')->where('id', $id)->update($edit_info);
        if ($edit_users == true) {
            return 'success';
        } else {
            return 'error';
        }
    }

    public static function add_users($info,$invite_code)
    {
        $check = Db::name('fd_user')->where('username',$info['username'])->count();
        if ($check == 0){
            $pid = Db::name('fd_user')->where('invite_code',$invite_code)->find();
            $is_two = false; //添加第二层，这个时候他的agent_id,agent_username就是他自己
            $agent_info['agent_id'] = "";
            $agent_info['agent_username']="";
            $table_id=0;
            if (!empty($pid)){
                $info['pid'] = $pid['id'];
                $info['invite_code'] = self::get_invite_code();
                
                    $info['agent_id']  = $pid['agent_id'];
                    $info['agent_username']=$pid['agent_username'];
                $table_id = $pid['agent_id'];
                
            }else{
                $is_two = true;
                $info['pid'] = 0;
                $info['invite_code'] = self::get_invite_code();
                $info['agent_id']=0;
                $info['agent_username']=$info['username'];
                
            }
            $add=false;
            $insert_id = Db::name('fd_user')->insertGetId($info);
            if ($insert_id>0){
                $add = true;
            }
                
            
            
            
            if ($add == true){
                return 'success';
            }else{
                return 'error';
            }
        }else{
            return 'error_use';
        }
    }

    public static function get_invite_code()
    {
        $invite_code = \app\index\model\StringCode::randString(6,1);
        $check_invite_code = Db::name('fd_user')->where('invite_code',$invite_code)->count();
//        echo Db::getLastSql();
        if ($check_invite_code > 0){
            self::get_invite_code();
        }else{
            return $invite_code;
        }
    }

    public static function edit_users_status($id, $status)
    {
        $status = intval($status);
        $id = intval($id);
        $res = Db::name('fd_user')->where('id', $id)->setField('status', $status);
        if ($res == true) {
            if (intval($status)==2){
                Db::name('fd_user')->where('id', $id)->setField('session_id',"123456");
            }
            return 'success';
        } else {
            return 'error';
        }
    }

    public static function user_level($id, $edit_info)
    {
        // var_dump($edit_info);die;
        $user_level = Db::name('fd_user')->where('id', $id)->update($edit_info);
        if ($user_level == true) {
            return 'success';
        } else {
            return 'error';
        }
    }

    public static function group_info()
    {
        $group_info = Db::name('fd_group')->order('id','desc')->select();
        return $group_info;
    }

    public static function user_group($id, $edit_info)
    {
        $user_level = Db::name('fd_user')->where('id', $id)->update($edit_info);
        if ($user_level == true) {
            return 'success';
        } else {
            return 'error';
        }
    }

    public static function level()
    {
        $level = Db::name('fd_level')->select();
        return $level;
    }

    public static function add_level($info)
    {
        $add_level = Db::name('fd_level')->insert($info);
        if ($add_level == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function level_info($id)
    {
        $level_info = Db::name('fd_level')->where('id',$id)->find();
        return $level_info;
    }

    public static function level_select()
    {
        $level_info = Db::name('fd_level')->select();
        return $level_info;
    }

    public static function edit_level($id,$info)
    {
        $edit_level = Db::name('fd_level')->where('id',$id)->update($info);
        if ($edit_level == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function del_level($id)
    {
        $del_level = Db::name('fd_level')->where('id',$id)->delete();
        if ($del_level == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function recharge($id,$money)
    {
        $user_info = Db::name('fd_user')->where('id',$id)->find();
        $info['uid'] = $user_info['id'];
        $info['pid'] = $user_info['pid'];
        $info['username'] = $user_info['username'];
        $info['phone'] = $user_info['phone'];
        $info['money'] = $money;
        $info['service_charge'] = 0;
        $info['type'] = 2;
        $info['status'] = 1;
        $info['create_time'] = time();
        $info['agent_id']=$user_info['agent_id'];
        $info['agent_username']=$user_info['agent_username'];
        $recharge_starts = Db::name('fd_recharge')->insertGetId($info);
        if ($recharge_starts >0){
            $recharge_money = Db::name('fd_user')->where('id',$user_info['id'])->setInc('balance',$money);
            if ($recharge_money == true){
                
                return 'success';
            }else{
                return 'error';
            }
        }else{
            return 'error_status';
        }
    }

    public static function day_order()
    {
        $user = Db::name('fd_user')->where('status',1)->select();
        foreach ($user as $k => $v){
            if ($v['day_o'] > $v['day_max_o']){
                Db::name('fd_user')->where('id',$v['id'])->setField('day_max_o',$v['day_o']);
            }
        }
    }

    public static function del_customer($id)
    {
        $del_customer = Db::name('fd_cs')->where('id',$id)->delete();
        if ($del_customer == true){
            return 'success';
        }else{
            return 'error';
        }
    }


}
