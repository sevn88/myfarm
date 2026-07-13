<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/1
 * Time: 23:19
 * QQ:1467572213
 */

namespace app\agent\model;

use think\Model;
use think\Db;
use think\facade\Config;
use \app\index\model\Base;

class Count extends Model
{
    public static function statistics($agent_id,$start_time,$end_time)
    {
        $s_time = strtotime($start_time);
        $e_time = strtotime($end_time) + 1 * 24 * 3600;


        //注册人数
        $reg_num = [
            ['pid', '=', $agent_id],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['reg_num'] = Db::name('fd_user')->where($reg_num)->count();

        //充值金额
        $c_recharge = [
            ['pid', '=', $agent_id],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['c_recharge'] =intval( Db::name('fd_recharge')->where($c_recharge)->sum('money'));


        //通道手续费
        $service_cz = [
            ['pid', '=', $agent_id],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['service_cz'] = intval(Db::name('fd_recharge')->where($service_cz)->sum('service_charge'));

        //手动充值
        $m_recharge = [
            ['pid', '=', $agent_id],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_recharge'] = intval(Db::name('fd_recharge')->where($m_recharge)->sum('money'));

        //提现金额
        $m_withdrawal = [
            ['pid', '=', $agent_id],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal'] = intval(Db::name('fd_withdrawal')->where($m_withdrawal)->sum('actual_money'));


        //提现手续费
        $m_withdrawal_cost = [
            ['pid', '=', $agent_id],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal_cost'] = Db::name('fd_withdrawal')->where($m_withdrawal_cost)->sum('service_money');


        // //抢单次数
        // $grab_num = [
        //     ['aid', '=', $agent_id],
        //     ['create_time', '>', $s_time],
        //     ['create_time', '<', $e_time],
        // ];
        // $info['grab_num'] = Db::name('fd_order')->where($grab_num)->count();


        // $z_recharge = Db::name('fd_recharge')->where($c_recharge)->count();
        // $s_recharge = Db::name('fd_recharge')->where($m_recharge)->count();

        // //充值次数
        // $info['r_number'] = $z_recharge + $s_recharge;
        
        //提现次数
        $info['withdrawal_number'] = Db::name('fd_withdrawal')->where($m_withdrawal)->count();
        
        //最新的手续费 提现金额*后台设置的提现收费比例+每笔20元（就是成功提现的次数）
        $info['m_withdrawal_cost_new'] = intval((float)$info['m_withdrawal']*(float)Base::get_config('tongji_tixian1') +(float)$info['withdrawal_number']*(float)Base::get_config('tongji_tixian3'));

        //首充次数
        //1、先获取统计时间之前的人的次数
        $where=[
            ['pid', '=', $agent_id],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '<', $s_time]
        ];
        $recharge_before = Db::field('uid')->distinct(true)->table('fd_recharge')->where($where)->select();
        
        $uid_recharge=[];
        foreach($recharge_before as $key => $val){
            $uid_recharge[]=$val['uid'];
        }
        //2、比较这个里面的人数
        $where=[
            ['pid', '=', $agent_id],
            ['uid','not in',$uid_recharge],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time','<',$e_time],
        ];
        $recharge_shouchong = Db::field('uid')->distinct(true)->table('fd_recharge')->where($where)->select();
        
        $info["shouchong"] = count($recharge_shouchong);

        //盈利金额
        $info['profit'] = $info['c_recharge'] + $info['m_recharge'] - $info['m_withdrawal'];

        //-------------------先加自身的   start
        //充值金额
        $c_recharge = [
            ['uid', '=', $agent_id],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['c_recharge'] =intval($info['c_recharge'] + Db::name('fd_recharge')->where($c_recharge)->sum('money'));


        //通道手续费
        $service_cz = [
            ['uid', '=', $agent_id],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['service_cz'] = intval($info['service_cz'] + Db::name('fd_recharge')->where($service_cz)->sum('service_charge'));

        //手动充值
        $m_recharge = [
            ['uid', '=', $agent_id],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_recharge'] = intval($info['m_recharge'] + Db::name('fd_recharge')->where($m_recharge)->sum('money'));

        //提现金额
        $m_withdrawal = [
            ['uid', '=', $agent_id],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal'] = intval($info['m_withdrawal'] + Db::name('fd_withdrawal')->where($m_withdrawal)->sum('actual_money'));


        //提现手续费
        $m_withdrawal_cost = [
            ['uid', '=', $agent_id],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal_cost'] =$info['m_withdrawal_cost'] + Db::name('fd_withdrawal')->where($m_withdrawal_cost)->sum('service_money');


        // //抢单次数
        // $grab_num = [
        //     ['uid', '=', $agent_id],
        //     ['create_time', '>', $s_time],
        //     ['create_time', '<', $e_time],
        // ];
        // $info['grab_num'] = $info['grab_num'] + Db::name('fd_order')->where($grab_num)->count();


        // $z_recharge = Db::name('fd_recharge')->where($c_recharge)->count();
        // $s_recharge = Db::name('fd_recharge')->where($m_recharge)->count();

        // //充值次数
        // $info['r_number'] =$info['r_number'] + $z_recharge + $s_recharge;
        
         //提现次数
        $z_withdrawal = Db::name("fd_withdrawal")->where($m_withdrawal)->count();
        $info['withdrawal_number'] =$info['withdrawal_number'] + $z_withdrawal;
        
        //最新的手续费 提现金额*后台设置的提现收费比例+每笔20元（就是成功提现的次数）
        $info['m_withdrawal_cost_new'] = intval((float)$info['m_withdrawal']*(float)Base::get_config('tongji_tixian1') +(float)$info['withdrawal_number']*(float)Base::get_config('tongji_tixian3'));

        //-------------------先加自身的   end


        //=======================取孩子的下级  start
        $son_ids = Db::name('fd_user')->field('id')->where('pid',$agent_id)->select();
        if (isset($son_ids)){
            if (count($son_ids)>0)
            {
                $ids_arr=[];
                foreach($son_ids as $key => $val){
                    $ids_arr[]=$val['id'];
                }
                $re = self::sub_index($start_time,$end_time,$ids_arr,$agent_id);
//                var_dump($re);
                $info["reg_num"] = $info["reg_num"]+$re["reg_num"];
                $info["c_recharge"] = intval($info["c_recharge"]+$re["c_recharge"]);
                $info["service_cz"] = intval($info["service_cz"]+$re["service_cz"]);
                $info["m_recharge"] = intval($info["m_recharge"]+$re["m_recharge"]);
                $info["m_withdrawal"] = intval($info["m_withdrawal"]+$re["m_withdrawal"]);
                $info["m_withdrawal_cost"] = $info["m_withdrawal_cost"]+$re["m_withdrawal_cost"];
                // $info["r_number"] = $info["r_number"]+$re["r_number"];
                // $info["grab_num"] = $info["grab_num"]+$re["grab_num"];
                $info["shouchong"]= $info["shouchong"] + $re["shouchong"];
                $info['withdrawal_number'] = $info['withdrawal_number']+ $re['withdrawal_number'];
                $info['m_withdrawal_cost_new']=intval($info['m_withdrawal_cost_new']+$re['m_withdrawal_cost_new']);

            }

        }
//        var_dump($ids_arr);
//        die;
        //=======================取孩子的下级  end
        //通道手续费  (手动充值+通道充值）*后台设置的一个系数
         $info['service_cz']=intval(($info['c_recharge']+$info['m_recharge'])*Base::get_config('tongji_chongzhi'));
        //盈利金额
        // $info['profit'] = $info['c_recharge'] + $info['m_recharge'] - $info['m_withdrawal'];
        $info['profit']=intval($info['c_recharge']+$info['m_recharge'] - $info['service_cz'] - $info['m_withdrawal'] - $info['m_withdrawal_cost_new']);
        $agent_rate = Db::name("fd_user")->where('id',$agent_id)->value('agent_rate');
        $info["profit_rate"] = intval(($info['c_recharge']+$info['m_recharge'])*intval($agent_rate)/100);
        $info['profit'] = $info['profit'] - $info['profit_rate'];
        return $info;
    }
    public static function sub_index($start_time,$end_time,$ids,$agent_id)

    {

        //这个是从父取子
        $s_time = strtotime($start_time);
        $e_time = strtotime($end_time) + 1 * 24 * 3600;


        //注册人数
        $reg_num = [
            ['pid', 'in', $ids],

            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['reg_num'] = Db::name('fd_user')->where($reg_num)->count();


        //充值金额
        $c_recharge = [
            ['pid', 'in', $ids],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['c_recharge'] = intval(Db::name('fd_recharge')->where($c_recharge)->sum('money'));

        // echo  Db::getLastSql();
        //通道手续费
        $service_cz = [
            ['pid', 'in', $ids],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['service_cz'] = intval(Db::name('fd_recharge')->where($service_cz)->sum('service_charge'));

        //手动充值
        $m_recharge = [
            ['pid', 'in', $ids],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_recharge'] = intval(Db::name('fd_recharge')->where($m_recharge)->sum('money'));

        //提现金额
        $m_withdrawal = [
            ['pid', 'in', $ids],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal'] = intval(Db::name('fd_withdrawal')->where($m_withdrawal)->sum('actual_money'));


        //提现手续费
        $m_withdrawal_cost = [
            ['pid', 'in', $ids],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal_cost'] = Db::name('fd_withdrawal')->where($m_withdrawal_cost)->sum('service_money');


        // //抢单次数
        // $grab_num = [
        //     ['aid', 'in', $ids],
        //     ['create_time', '>', $s_time],
        //     ['create_time', '<', $e_time],
        // ];
        // $info['grab_num'] = Db::name('fd_order')->where($grab_num)->count();


        // $z_recharge = Db::name('fd_recharge')->where($c_recharge)->count();
        // $s_recharge = Db::name('fd_recharge')->where($m_recharge)->count();

        // //充值次数
        // $info['r_number'] = $z_recharge + $s_recharge;
        
        //提现次数
        $withdraw_number = Db::name('fd_withdrawal')->where($m_withdrawal)->count();
        $info['withdrawal_number'] = $withdraw_number;
        
         //最新的手续费 提现金额*后台设置的提现收费比例+每笔20元（就是成功提现的次数）
        $info['m_withdrawal_cost_new'] =intval( (float)$info['m_withdrawal']*(float)Base::get_config('tongji_tixian1') +(float)$info['withdrawal_number']*(float)Base::get_config('tongji_tixian3'));

        //首充次数
        //1、先获取统计时间之前的人的次数
        $where=[
            ['pid', 'in', $ids],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '<', $s_time]
        ];
        $recharge_before = Db::field('uid')->distinct(true)->table('fd_recharge')->where($where)->select();
        
        $uid_recharge=[];
        foreach($recharge_before as $key => $val){
            $uid_recharge[]=$val['uid'];
        }
        //2、比较这个里面的人数
        $where=[
            ['pid', 'in', $ids],
            ['uid','not in',$uid_recharge],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time','<',$e_time],
        ];
        
        $recharge_shouchong = Db::field('uid')->distinct(true)->table('fd_recharge')->where($where)->select();
        
        $info["shouchong"] = count($recharge_shouchong);
        
        // var_dump($recharge_shouchong);die;

        //=======================取孩子的下级  start
        $where=[
            ['pid','in',$ids],
        ];
        $son_ids = Db::name('fd_user')->field('id')->where($where)->select();
        if (isset($son_ids)){
            if (count($son_ids)>0)
            {
                $ids_arr=[];
                foreach($son_ids as $key => $val){
                    $ids_arr[]=$val['id'];
                }
                $re = self::sub_index($start_time,$end_time,$ids_arr,$agent_id);

                $info["reg_num"] = $info["reg_num"]+$re["reg_num"];
                $info["c_recharge"] = $info["c_recharge"]+$re["c_recharge"];
                $info["service_cz"] = $info["service_cz"]+$re["service_cz"];
                $info["m_recharge"] = $info["m_recharge"]+$re["m_recharge"];
                $info["m_withdrawal"] = $info["m_withdrawal"]+$re["m_withdrawal"];
                $info["m_withdrawal_cost"] = $info["m_withdrawal_cost"]+$re["m_withdrawal_cost"];
                // $info["r_number"] = $info["r_number"]+$re["r_number"];
                // $info["grab_num"] = $info["grab_num"]+$re["grab_num"];
                $info["shouchong"]= $info["shouchong"] + $re["shouchong"];
                $info['withdrawal_number'] = $info['withdrawal_number']+ $re['withdrawal_number'];
                $info['m_withdrawal_cost_new']=$info['m_withdrawal_cost_new']+$re['m_withdrawal_cost_new'];
            }

        }

        //=======================取孩子的下级  end
        return $info;
    }
    public static function recharge_list($agent_id,$start_time,$end_time){
        $s_time = strtotime($start_time);
        $e_time = strtotime($end_time) + 1 * 24 * 3600;

//        if ($pid){
//            $agent_id = Db::name('fd_user')->where('username',$pid)->value('id');
//        }
        $agent_name = Db::name('fd_user')->where('id',$agent_id)->value('username');

        $ids=[];
        $ids[]=["id"=>$agent_id,'username'=>$agent_name];
        $list = Db::name("fd_user")->where('pid',$agent_id)->field('id,username')->select();
        $ids = array_merge($ids,$list);

        foreach($ids as $key => $val){
            $id=$val['id'];
            $res = Self::get_count_personal($s_time,$e_time,$id,$agent_id,$start_time,$end_time);
            if (isset($res)){
                $ids[$key]=array_merge($val,$res);
            }

        }
        return $ids;
//        var_dump($ids);die;

    }
    public static function recharge_type()
    {
        $recharge_type = Db::name('fd_recharge_type')->select();
        return $recharge_type;
    }
    public static function get_count_personal($s_time,$e_time,$id,$agent_id,$start_time,$end_time){
        //-------------------先加自身的   start
        //注册人数
        $c_recharge = [
            ['pid', '=', $id],

            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['reg_num'] = Db::name('fd_user')->where($c_recharge)->count();


        //充值金额
        $c_recharge = [
            ['uid', '=', $id],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['c_recharge'] = intval(Db::name('fd_recharge')->where($c_recharge)->sum('money'));


        //通道手续费
        $service_cz = [
            ['uid', '=', $id],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['service_cz'] =  intval(Db::name('fd_recharge')->where($service_cz)->sum('service_charge'));

        //手动充值
        $m_recharge = [
            ['uid', '=', $id],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_recharge'] = intval(Db::name('fd_recharge')->where($m_recharge)->sum('money'));

        //提现金额
        $m_withdrawal = [
            ['uid', '=', $id],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal'] =  intval(Db::name('fd_withdrawal')->where($m_withdrawal)->sum('actual_money'));


        //提现手续费
        $m_withdrawal_cost = [
            ['uid', '=', $id],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal_cost'] = Db::name('fd_withdrawal')->where($m_withdrawal_cost)->sum('service_money');


        // //抢单次数
        // $grab_num = [
        //     ['uid', '=', $id],
        //     ['create_time', '>', $s_time],
        //     ['create_time', '<', $e_time],
        // ];
        // $info['grab_num'] =  Db::name('fd_order')->where($grab_num)->count();


        //首充次数
        //1、先获取统计时间之前的人的次数
        $where=[
            ['uid', '=', $id],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '<', $s_time]
        ];
        
        $recharge_before=  Db::field('uid')->distinct(true)->table('fd_recharge')->where($where)->select();
        
        if (count($recharge_before)>0){
            $info["shouchong"]=0;
        }else{
            $where=[
                ['uid', '=', $id],
                ['type', '=', 1],
                ['status', '=', 1],
                ['create_time', '>', $s_time],
                ['create_time','<',$e_time],
            ];
            $recharge_shouchong =Db::field('uid')->distinct(true)->table('fd_recharge')->where($where)->select();
            if (count($recharge_shouchong)>0){
                $info["shouchong"]=1;
            }else{
                $info["shouchong"]=0;
            }
        }
        
        


        // $z_recharge = Db::name('fd_recharge')->where($c_recharge)->count();
        // $s_recharge = Db::name('fd_recharge')->where($m_recharge)->count();

        // //充值次数
        // $info['r_number'] = $z_recharge + $s_recharge;

        //提现次数
        $withdraw_number = Db::name('fd_withdrawal')->where($m_withdrawal)->count();
        $info['withdrawal_number'] = $withdraw_number;
        
        
        //最新的手续费 提现金额*后台设置的提现收费比例+每笔20元（就是成功提现的次数）
        $info['m_withdrawal_cost_new'] =intval( (float)$info['m_withdrawal']*(float)Base::get_config('tongji_tixian1') +(float)$info['withdrawal_number']*(float)Base::get_config('tongji_tixian3'));
        
        //=======================取孩子的下级  start
        //不对查询者本身去找下级
        if ($id!=$agent_id){
            $where=[
                ['pid','=',$id],
            ];
            $son_ids = Db::name('fd_user')->field('id')->where($where)->select();
            if (isset($son_ids) ){
                if (count($son_ids)>0)
                {
                    $ids_arr=[];
                    foreach($son_ids as $key => $val){
                        $ids_arr[]=$val['id'];
                    }

                    $re = self::sub_index_self($start_time,$end_time,$ids_arr);
                    //  var_dump($ids_arr);
                    //  echo("<br>");
                    //  var_dump($re);
                    //  die;
                    $info["reg_num"] = $info["reg_num"]+$re["reg_num"];
                    $info["c_recharge"] = intval($info["c_recharge"]+$re["c_recharge"]);
                    $info["service_cz"] = intval($info["service_cz"]+$re["service_cz"]);
                    $info["m_recharge"] = intval($info["m_recharge"]+$re["m_recharge"]);
                    $info["m_withdrawal"] = intval($info["m_withdrawal"]+$re["m_withdrawal"]);
                    $info["m_withdrawal_cost"] = $info["m_withdrawal_cost"]+$re["m_withdrawal_cost"];
                    // $info["r_number"] = $info["r_number"]+$re["r_number"];
                    // $info["grab_num"] = $info["grab_num"]+$re["grab_num"];
                    $info["shouchong"] = $info["shouchong"] + $re["shouchong"];
                    $info['withdrawal_number'] = $info['withdrawal_number']+$re['withdrawal_number'];
                    $info['m_withdrawal_cost_new']=intval($info['m_withdrawal_cost_new']+$re['m_withdrawal_cost_new']);

                }

            }
        }

        //=======================取孩子的下级  end



        //通道手续费  (手动充值+通道充值）*0.2
        $info['service_cz']=intval(($info['c_recharge']+$info['m_recharge'])*Base::get_config('tongji_chongzhi'));
        //盈利金额
        // $info['profit'] = $info['c_recharge'] + $info['m_recharge'] - $info['m_withdrawal'];
        $info['profit']=intval($info['c_recharge']+$info['m_recharge'] - $info['service_cz'] - $info['m_withdrawal'] - $info['m_withdrawal_cost_new']);
        $agent_rate = Db::name('fd_user')->where('id',$id)->value('agent_rate');
        $info['profit_rate'] = intval(($info['c_recharge']+$info['m_recharge'])*intval($agent_rate)/100);
        $info['profit'] = $info['profit'] - $info['profit_rate'];
        return $info;
        //-------------------先加自身的   end

    }



    public static function sub_index_self($start_time,$end_time,$ids)
    {

        //这个应从自身取，而不是从父取子
        $s_time = strtotime($start_time);
        $e_time = strtotime($end_time) + 1 * 24 * 3600;


        //注册人数
        $reg_num = [
            ['pid', 'in', $ids],

            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['reg_num'] = Db::name('fd_user')->where($reg_num)->count();


        //充值金额
        $c_recharge = [
            ['uid', 'in', $ids],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['c_recharge'] = intval(Db::name('fd_recharge')->where($c_recharge)->sum('money'));

        // echo  Db::getLastSql();
        //通道手续费
        $service_cz = [
            ['uid', 'in', $ids],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['service_cz'] = intval(Db::name('fd_recharge')->where($service_cz)->sum('service_charge'));

        //手动充值
        $m_recharge = [
            ['uid', 'in', $ids],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_recharge'] = intval(Db::name('fd_recharge')->where($m_recharge)->sum('money'));

        //提现金额
        $m_withdrawal = [
            ['uid', 'in', $ids],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal'] = intval(Db::name('fd_withdrawal')->where($m_withdrawal)->sum('actual_money'));


        //提现手续费
        $m_withdrawal_cost = [
            ['uid', 'in', $ids],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal_cost'] = Db::name('fd_withdrawal')->where($m_withdrawal_cost)->sum('service_money');


        // //抢单次数
        // $grab_num = [
        //     ['uid', 'in', $ids],
        //     ['create_time', '>', $s_time],
        //     ['create_time', '<', $e_time],
        // ];
        // $info['grab_num'] = Db::name('fd_order')->where($grab_num)->count();


        // $z_recharge = Db::name('fd_recharge')->where($c_recharge)->count();
        // $s_recharge = Db::name('fd_recharge')->where($m_recharge)->count();

        // //充值次数
        // $info['r_number'] = $z_recharge + $s_recharge;
        
        //提现次数
        $withdraw_number = Db::name("fd_withdrawal")->where($m_withdrawal)->count();
        $info['withdrawal_number']=$withdraw_number;
        
        //最新的手续费 提现金额*后台设置的提现收费比例+每笔20元（就是成功提现的次数）
        $info['m_withdrawal_cost_new'] = intval((float)$info['m_withdrawal']*(float)Base::get_config('tongji_tixian1') +(float)$info['withdrawal_number']*(float)Base::get_config('tongji_tixian3'));


        //首充次数
        //1、先获取统计时间之前的人的次数
        $where=[
            ['uid', 'in', $ids],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '<', $s_time]
        ];
        $recharge_before=  Db::field('uid')->distinct(true)->table('fd_recharge')->where($where)->select();;
        $uid_recharge=[];
        foreach($recharge_before as $key => $val){
            $uid_recharge[]=$val['uid'];
        }
        //2、比较这个里面的人数
        $where=[
            ['uid', 'in', $ids],
            ['uid','not in',$uid_recharge],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time','<',$e_time],
        ];
        $recharge_shouchong =Db::field('uid')->distinct(true)->table('fd_recharge')->where($where)->select();;
        $info["shouchong"] = count($recharge_shouchong);

        //=======================取孩子的下级  start
        $where=[
            ['pid','in',$ids],
        ];
        $son_ids = Db::name('fd_user')->field('id')->where($where)->select();
        if (isset($son_ids)){
            if (count($son_ids)>0)
            {
                $ids_arr=[];
                foreach($son_ids as $key => $val){
                    $ids_arr[]=$val['id'];
                }
                $re = self::sub_index_self($start_time,$end_time,$ids_arr);

                $info["reg_num"] = $info["reg_num"]+$re["reg_num"];
                $info["c_recharge"] = $info["c_recharge"]+$re["c_recharge"];
                $info["service_cz"] = $info["service_cz"]+$re["service_cz"];
                $info["m_recharge"] = $info["m_recharge"]+$re["m_recharge"];
                $info["m_withdrawal"] = $info["m_withdrawal"]+$re["m_withdrawal"];
                $info["m_withdrawal_cost"] = $info["m_withdrawal_cost"]+$re["m_withdrawal_cost"];
                // $info["r_number"] = $info["r_number"]+$re["r_number"];
                // $info["grab_num"] = $info["grab_num"]+$re["grab_num"];
                $info["shouchong"] =  $info["shouchong"] + $re["shouchong"];
                $info['withdrawal_number']=$info["withdrawal_number"]+$re['withdrawal_number'];
                $info['m_withdrawal_cost_new']=$info['m_withdrawal_cost_new']+$re['m_withdrawal_cost_new'];

            }

        }

        //=======================取孩子的下级  end
        return $info;
    }
}