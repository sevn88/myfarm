<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/26
 * Time: 12:51
 * QQ:1467572213
 */

namespace app\agent\model;

use think\Db;
use think\facade\Config;
use think\Model;

class Recharge extends Model
{
    public static function recharge_list($keyword,$agent_id,$userid,$usertel,$status,$order_id)
    {
        $where = array();
        // $where[]=array('pid','=', $agent_id);
//        $where['status'] = 1;

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
        if ($keyword || $usertel || $userid || $status!='99' || $order_id){

            if ($keyword){
                $where[] = array('username','like',"%$keyword%");
            }
            if ($userid){
                $where[] = array('uid','like',"%$userid%");
            }
            if ($order_id){
                $where[] = array('order_id','like',"%$order_id%");
            }
            if ($usertel){
                $where[] = array('phone','like', "%$usertel%");
            }
            if ($status!='99' && isset($status)){
                $where[]=array('status','=',$status);
            }
            $list = Db::name('fd_recharge')
                ->where($where)
//                ->where('username',$keyword)
                ->order('id','desc')
                ->paginate(10,false,['query'=>request()->param()])->each(function($item,$key){
                    $item["pusername"]=Db::name('fd_user')->where('id',$item["pid"])->field('username')->value('username');
                    return $item;});
            // echo  Db::getLastSql();
            // echo "aaaa";
            return $list;
        }else{
            $list = Db::name('fd_recharge')
                ->where($where)
                ->order('id','desc')
                ->paginate(10,false,['query'=>request()->param()])->each(function($item,$key){
                    $item["pusername"]=Db::name('fd_user')->where('id',$item["pid"])->field('username')->value('username');
                    return $item;});
            // echo  Db::getLastSql();
            // echo "bbbbb";
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

    public static function sum($keyword,$agent_id,$status)
    {
        $where['pid'] = $agent_id;
        $where['username'] = $keyword;
        if ($status!='99'){
            $where['status'] = $status;
        }


        $sum = Db::name('fd_recharge')->where($where)->sum('money');
        return $sum;


    }

    public static function recharge_type()
    {
        $recharge_type = Db::name('fd_recharge_type')->select();
        return $recharge_type;
    }

}