<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/2
 * Time: 9:03
 * QQ:1467572213
 */

namespace app\agent\model;

use think\Model;
use think\Db;
use think\facade\Session;
use think\facade\Config;

class User extends Model
{
    public static function index($agent_id, $keyword,$userid,$usertel,$userip)
    {
        
        $where=array();
        // $where[] = array('pid','=',$agent_id);
        
        //首先获取下级的孩子
        $son_arr =Db::name('fd_user')->field('id')->where('pid',$agent_id)->select();
        $son_ids=[];
        foreach($son_arr as $key => $val){
            $son_ids[]=$val['id'];
        }
      
        // if (count($son_ids)<=0){
        //     return [];
        // }
        // var_dump($son_ids);die;
        
        $re = self::ids_from_parent($son_ids);
        if (count($re)>0){
            $son_ids = array_merge($son_ids,$re);
        }
        // echo(count($son_ids)); die;
        $son_ids=array_unique($son_ids);
        // var_dump($son_ids);die;
        $where[]=array('id','in',$son_ids);
        
        
        if ($keyword || $usertel || $userid || $userip) {
        
            if ($keyword){
                $where[] = array('username','like',"%$keyword%");
            }
            if ($userid){
                $where[] = array('id','like',"%$userid%");
            }
            if ($usertel){
                $where[] = array('phone','like', "%$usertel%");
            }
            if ($userip){
                $where[] = array('reg_ip','like', "%$userip%");
            }
        
            $list = Db::name('fd_user')
                ->where($where)
                ->order('id', 'desc')
                ->paginate(10, false, ['query' => request()->param()])->each(function($item,$key){
                    $item["pusername"]=Db::name('fd_user')->where('id',$item["pid"])->field('username')->value('username');
                    return $item;});;
//            echo Db::getLastSql();
            return $list;
        } else {
            $list = Db::name('fd_user')
                ->where($where)
                ->order('id', 'desc')
                ->paginate(10, false, ['query' => request()->param()])->each(function($item,$key){
                    $item["pusername"]=Db::name('fd_user')->where('id',$item["pid"])->field('username')->value('username');
                    return $item;});;
            return $list;
        }
    }
    
    public static function ids_from_parent($ids){
        if (count($ids)<=0){
            return [];
        }
        $where =[
            ['pid','in',$ids]
            ];
        
        $son_arr = Db::name('fd_user')->field('id')->where($where)->select();
        
        $son_ids=[];
        
        if (count($son_arr)>0) {
            // $son_ids=[];
            
            foreach($son_arr as $key => $val){
                    $son_ids[]=$val['id'];
                }
            
            $re = Self::ids_from_parent($son_ids);
            if (count($re)>0){
                $son_ids = array_merge($son_ids,$re);
            }
            
        }
        
        
        // $a=[1,2,3];
        // $b=[4,3,2,5];
        // $c = array_merge($a,$b);
        // $c = array_unique($c); //去重
        // // $c = $a+$b;
        
        return $son_ids;
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

    public static function user_info($agent_id,$id)
    {
        $where['pid'] = $agent_id;
        $where['id'] = $id;
        $user_info = Db::name('fd_user')->where($where)->find();
        $user_info['agent_role']=Session::get("role");
//        print_r($user_info);die;
        return $user_info;
    }
    
    public static function user_info_not_agent($agent_id,$id) //针对上面的函数而言的。原先这个功能只是要求代理可以编辑下级，现在是要求编辑下级以及下级的下级。所以这个修改
    {
        // $where['pid'] = $agent_id;
        $where['id'] = $id;
        $user_info = Db::name('fd_user')->where($where)->find();
        $user_info['agent_role']=Session::get("role");
//        print_r($user_info);die;
        return $user_info;
    }

    public static function level_info()
    {
        $level_info = Db::name('fd_level')->select();
        return $level_info;
    }

    public static function edit($agent_id,$id,$info)
    {
        // $where['pid'] = $agent_id;
        $where['id'] = $id;
        $user_info = Db::name('fd_user')->where($where)->find();
        if (isset($info['agent_rate'])){
            if ($user_info['agent_rate']!=$info['agent_rate'] && $id==$user_info['agent_id']){
                Db::name('fd_user')->where('agent_id', (int)$id)->update(['agent_rate' => (int)$info['agent_rate']]);
            }
            if ($user_info['agent_rate']!=$info['agent_rate'] && intval($info['role'])==3){
                Db::name('fd_user')->where('pid', (int)$id)->update(['agent_rate' => (int)$info['agent_rate']]);
            }
        }
        $edit = Db::name('fd_user')->where($where)->update($info);

        if ($edit == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function get_group_info()
    {
        $group_info = Db::name('fd_group')->select();
        return $group_info;
    }

    public static function set_group($uid,$group_id)
    {
        $where['id'] = $uid;
        $set_group = Db::name('fd_user')->where($where)->setField('group',$group_id);
        if ($set_group == true){
            Db::name('fd_user')->where($where)->setField('group_order',0);
            return 'success';
        }else{
            return 'error';
        }
    }


    public static function recharge($id,$money)
    {
        $agent_id= Session::get("agent_id");
        $user_info = Db::name('fd_user')->where('id',$id)->find();
        $user_agent_info = Db::name('fd_user')->where('id',$agent_id)->find();
        $info['uid'] = $user_info['id'];
        $info['pid'] = $user_info['pid'];
        $info['username'] = $user_info['username'];
        $info['phone'] = $user_info['phone'];
        $info['money'] = $money;
        $info['service_charge'] = 0;
        $info['type'] = 3;
        $info['status'] = 1;
        $info['create_time'] = time();
        $info['by_agent_id'] = $agent_id;
        $info['by_agent_username']=$user_agent_info['username'];
        $info['agent_id'] = $user_info['agent_id'];
        $info['agent_username']=$user_info['agent_username'];
        $recharge_starts = Db::name('fd_recharge')->insertGetId($info);
        if ($recharge_starts >0){
            // Db::execute("insert into fd_recharge_".strval($user_info['agent_id'])." select * from fd_recharge where id=".strval($recharge_starts));
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

}