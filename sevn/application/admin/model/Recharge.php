<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/25
 * Time: 1:18
 * QQ:1467572213
 */

namespace app\admin\model;

use think\Model;
use think\Db;
use think\facade\Config;
use think\facade\Session;

class Recharge extends Model
{
    public static function index($keyword,$userid,$usertel,$status,$order_no,$agentname)
    {
        if ($keyword || $userid || $usertel || $status!='99' || $order_no || $agentname) {
            $where = array();
            if ($keyword){
                $where[]=array('username','like',"%$keyword%");
            }
            if ($userid){
                $where[]=array('uid','like',"%$userid%");
            }
            if ($order_no){
                $where[]=array('order_id','like',"%$order_no%");
            }
            if ($usertel){
                $where[]=array('phone','like',"%$usertel%");
            }
            if ($status!=99){
                if ($status!='99'){
                    $where[]=array('status','=',$status);
                }
            }
            if ($agentname){
                $where[]=array('by_agent_username','like','%'.$agentname.'%');
            }
            // var_dump($where);
            $list = Db::name('fd_recharge')->where($where)->order('id','desc')
                ->paginate(10, false, ['query' => request()->param()])->each(function($item,$key){
                    $item["pusername"]=Db::name('fd_user')->where('id',$item["pid"])->field('username')->value('username');
                    return $item;});
            // var_dump(Db::getLastSql());
            return $list;
        } else {
            $list = Db::name('fd_recharge')->order('id', 'desc')
                ->paginate(10, false, ['query' => request()->param()])->each(function($item,$key){
                    $item["pusername"]=Db::name('fd_user')->where('id',$item["pid"])->field('username')->value('username');
                    return $item;});
            
            return $list;
        }
    }

    public static function sum($keyword,$userid,$usertel,$status,$order_no,$agentname)
    {
        if ($keyword || $userid || $usertel || $status || $agentname){
            $where = array();
            if ($keyword){
                $where[]=array('username','like',"%$keyword%");
            }
             if ($order_no){
                $where[]=array('order_no','like',"%$order_no%");
            }
            if ($userid){
                $where[]=array('uid','like',"%$userid%");
            }
            if ($usertel){
                $where[]=array('phone','like',"%$usertel%");
            }
            if ($status){
                if ($status!='99'){
                    $where[]=array('status','=',$status);
                }
            }
            if ($agentname){
                $where[]=array('by_agent_username','like','%'.$agentname.'%');
            }
            $list = Db::name('fd_recharge')->where($where)->where('status',1)->sum('money');
            return $list;
        }
    }

    public static function recharge_mode()
    {
        return Db::name('fd_recharge_api')->select();
    }

    public static function recharge_add($info)
    {
        $add = Db::name('fd_recharge_api')->insert($info);
        if ($add == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function recharge_status($id,$status)
    {
        $status = Db::name('fd_recharge_api')->where('id',$id)->setField('status',$status);
        if ($status == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function recharge_type()
    {
        $recharge_type = Db::name('fd_recharge_type')->select();
        return $recharge_type;
    }


}