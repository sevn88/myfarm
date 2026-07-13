<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/8
 * Time: 9:41
 * QQ:1467572213
 */

namespace app\agent\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Withdrawal extends Model
{
    public static function index($agent_id,$keyword,$userid,$usertel,$status,$order_no)
    {
        $where=array();
        // $where[]=array('pid','=',$agent_id);
        $where[]=array('type','=',2);
        //首先获取下级的孩子
        $son_arr =Db::name('fd_user')->field('id')->where('pid',$agent_id)->select();
        $son_ids=[];
        foreach($son_arr as $key => $val){
            $son_ids[]=$val['id'];
        }
        // var_dump($son_ids);die;
        $re = self::ids_from_parent($son_ids);
        if (count($re)>0){
            $son_ids = array_merge($son_ids,$re);
        }
        // echo(count($son_ids));
        $son_ids=array_unique($son_ids);
        // var_dump($son_ids);die;
        $where[]=array('uid','in',$son_ids);
    
//        $where['pid'] = $agent_id;
//        $where['type'] = 2;
        if ($keyword || $userid || $usertel || (isset($status) && $status!='99') || $order_no){
            // var_dump($status);die;
            if ($keyword){
                $where[] = array('username','like',"%$keyword%");
            }
            if ($userid){
                $where[] = array('uid','like',"%$userid%");
            }
            if ($usertel){
                $where[] = array('phone','like', "%$usertel%");
            }
            if ($status!='99' && isset($status)){
                $where[]=array('status','=',$status);
            }
            if ($order_no){
                $where[] = array('order_no','like', "%$order_no%");
            }

            $list = Db::name('fd_withdrawal')
                ->where($where)
                ->order('id','desc')
                ->paginate(10,false,['query'=>request()->param()])->each(function($item,$key){
                    $item["pusername"]=Db::name('fd_user')->where('id',$item["pid"])->field('username')->value('username');
                    return $item;});;
            // echo  Db::getLastSql();
            return $list;
        }else{
            
            
            $list = Db::name('fd_withdrawal')
                ->where($where)
                ->order('id','desc')
                ->paginate(10,false,['query'=>request()->param()])->each(function($item,$key){
                    $item["pusername"]=Db::name('fd_user')->where('id',$item["pid"])->field('username')->value('username');
                    return $item;});;
            // echo  Db::getLastSql();
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

    public static function withdrawal_pass($agent_id,$id)
    {
        $where['id'] = $id;
        $where['pid'] = $agent_id;
        $where['agent_check'] = 0;
        $where['type'] = 2;
        $pass = Db::name('fd_withdrawal')->where($where)->setField('agent_check',1);
        if ($pass == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function withdrawal_refuse($agent_id,$id)
    {
        $where['id'] = $id;
        $where['pid'] = $agent_id;
        $where['agent_check'] = 0;
        $where['type'] = 2;
        $refuse_info = Db::name('fd_withdrawal')->where($where)->find();
        if ($refuse_info){
            $refuse = Db::name('fd_withdrawal')->where($where)->setField('agent_check',2);
            if ($refuse == true){
                $inc_money = Db::name('fd_user')->where('id',$refuse_info['uid'])->setInc('balance',$refuse_info['apply_money']);
                if ($inc_money == true){
                    return 'success';
                }else{
                    return 'error_money';
                }
            }else{
                return 'error_status';
            }
        }else{
            return 'error_info';
        }
    }
    
    
   public static function withdrawal_type()
    {
        $withdrawal_type = Db::name('fd_withdraw_type')->select();
        return $withdrawal_type;
    }

}