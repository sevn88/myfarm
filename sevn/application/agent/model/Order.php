<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/14
 * Time: 23:00
 * QQ:1467572213
 */

namespace app\agent\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Order extends Model
{
    public static function order_list($agent_id,$keyword,$userid,$usertel)
    {
        
        $where = array();
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
    
        if ($keyword || $usertel || $userid){
            
            if ($keyword){
                $where[] = array('username','like',"%$keyword%");
            }
            if ($userid){
                $where[] = array('uid','like',"%$userid%");
            }
            if ($usertel){
                $where[] = array('phone','like', "%$usertel%");
            }
            // $where[] = array('aid','=',$agent_id);
            // $list = Db::name('fd_order')
            //     ->where($where)
            //     ->order('id', 'desc')
            //     ->paginate(10, false, ['query' => request()->param()]);
                
             $list = Db::name('fd_order')
                ->where($where)
                ->order('id','desc')
                ->paginate(10,false,['query'=>request()->param()])->each(function($item,$key){
                    $item["p_user_balance"]=Db::name('fd_user')->where('id',$item["uid"])->field('balance')->value('balance');
                    return $item;});
        }else{
           $list = Db::name('fd_order')
                ->where($where)
                ->order('id','desc')
                ->paginate(10,false,['query'=>request()->param()])->each(function($item,$key){
                    $item["p_user_balance"]=Db::name('fd_user')->where('id',$item["uid"])->field('balance')->value('balance');
                    return $item;});
        }
        return $list;
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
    public static function order_rollover($id,$hour)
    {
        $update['sub_time'] = time() + $hour * 3600;
        $update['status'] = 0;
        $order_rollover = Db::name('fd_order')->where('id',$id)->update($update);
        if ($order_rollover == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function order_release($id)
    {
        $order_release = Db::name('fd_order')->where('id',$id)->setField('status',4);
        if ($order_release == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function get_goods_list($goods_name,$money_min,$money_max)
    {
        if (!empty($goods_name) || !empty($money_min) || !empty($money_max)){
            if (!empty($goods_name)){
                $map[] = ['goods_name','like',"%$goods_name%"];
            }
            if (!empty($money_min)){
                $map[] = ['goods_price','>',$money_min];
            }
            if (!empty($money_max)){
                $map[] = ['goods_price','<',$money_max];
            }
            $goods_list = Db::name('fd_goods_list')
                ->where($map)
                ->order('id','desc')
                ->paginate(10,false,['query'=>request()->param()]);
        } else {
            $goods_list = Db::name('fd_goods_list')
                ->where('status', 1)
                ->order('id', 'desc')
                ->paginate(10, false, ['query' => request()->param()]);
        }
        return $goods_list;
    }

    public static function add($oid,$goods_id)
    {
        $order_info = Db::name('fd_order')->where('id',$oid)->find();
        $user_info = Db::name('fd_user')->where('id',$order_info['uid'])->find();
        foreach ($goods_id as $k => $v){
            $add_info['order_id'] = 'A'.date('md').time().mt_rand(111,999);
            $add_info['aid'] = $order_info['aid'];
            $add_info['uid'] = $order_info['uid'];
            $add_info['username'] = $order_info['username'];
            $add_info['phone'] = $order_info['phone'];
            $add_info['grab_type'] = $order_info['grab_type'];
            $add_info['goods_id'] = $v;
            $goods_info = Db::name('fd_goods_list')->where('id',$v)->find();
            $add_info['goods_name'] = $goods_info['goods_name'];
            $add_info['goods_price'] = $goods_info['goods_price'];
            $add_info['goods_pic'] = $goods_info['goods_pic'];
            $add_info['goods_type'] = $order_info['goods_type'];
            $add_info['user_balance'] = $user_info['balance'];

            $add_info['order_commission'] = $order_info['order_commission'];
            if ($order_info['order_commission'] == 0){
                $add_info['order_earnings'] = $order_info['order_earnings'];
            }else{
                $add_info['order_earnings'] = $goods_info['goods_price'] * $order_info['order_commission'];
            }
            $add_info['s_num'] = $order_info['s_num'] + 1;
            $add_info['e_num'] = $order_info['e_num'] + 1;
            $add_info['status'] = 0;
            $add_info['sub_time'] =  time() + 1 * 3600;
            $add_info['create_time'] =  time();
            Db::name('fd_order')->insert($add_info);
        }
        return 'success';
    }

    public static function order_del($id)
    {
        $del = Db::name('fd_order')->where('id',$id)->delete();
        if ($del == true){
            return 'success';
        }else{
            return 'error';
        }
    }

}