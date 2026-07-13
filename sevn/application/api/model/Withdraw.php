<?php

namespace app\api\model;

use think\Model;
use think\Db;

class Withdraw extends Model
{
    public function pay51($info)
    {
        $list = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->find();



        if ($list) {
            $re=false;

            if ($info['status']==1){
                $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',1);
            }else
            {
                if ($list['status']!=2 && $list['status']!=4){
                    $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',5);
                }
            }
            if ($re == true) {
                return "SUCCESS";
            } else {
                return "ERROR";
            }
        } else {
            return "ERROR";
        }


    }
    public function htpay($info)
    {
        $list = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->find();

        if ($list) {
            $re=false;

            if ($info['status']==1){
                $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',1);
                if ($info['status'] == 1 ) {
                    $recharge_info = $list;
                    if (strval($recharge_info['status'])!='1'){
                        // 分表已移除，所有数据在主表中通过 agent_id 字段区分
                    }
                   
                }

            }else
            {
                if ($list['status']!=2 && $list['status']!=4){
                    $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',5);
                }
            }
            if ($re == true) {
                return "SUCCESS";
            } else {
                return "ERROR";
            }
        } else {
            return "ERROR";
        }


    }
    public function dzxum($info)
    {
        $list = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->find();



        if ($list) {
            $re = false;

            if ($info['status']==1){
                $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',1);
            }else
            {
                if ($list['status']!=2 && $list['status']!=4){
                    $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',5);
                }
            }
            if ($re == true) {
                return "SUCCESS";
            } else {
                return "ERROR";
            }
        } else {
            return "ERROR";
        }


    }
     public function fastpay($info)
    {
        $list = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->find();



        if ($list) {
            $re = false;
            if ($list['actual_money']!= $info['money_real']){
                return "ERROR";
            }

            if ($info['status']==1){
                $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',1);
            }else
            {
                if ($list['status']!=2 && $list['status']!=4){
                    $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',5);
                }
            }
            if ($re == true) {
                return "SUCCESS";
            } else {
                return "ERROR";
            }
        } else {
            return "ERROR";
        }


    }
    
     public function fastpay2($info)
    {
        $list = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->find();



        if ($list) {
            $re = false;
            if ($list['actual_money']!= $info['money_real']){
                return "ERROR";
            }

            if ($info['status']==1){
                $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',1);
            }elseif ($info['status']==3){
                $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',3);
            }else
            {
                if ($list['status']!=2 && $list['status']!=4){
                    $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',5);
                }
            }
            if ($re == true) {
                return "SUCCESS";
            } else {
                return "ERROR";
            }
        } else {
            return "ERROR";
        }


    }
    
    public function sunpay($info)
    {
        $list = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->find();



        if ($list) {
            $re = false;

            if ($info['status']==1){
                $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',1);
            }else
            {
                if ($list['status']!=2 && $list['status']!=4){
                    $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',5);
                }
            }
            if ($re == true) {
                return "SUCCESS";
            } else {
                return "ERROR";
            }
        } else {
            return "ERROR";
        }


    }
    
    
    public function lepay($info)
    {
        $list = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->find();



        if ($list) {
            $re = false;

            if ($info['status']==1){
                $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',1);
            }else
            {
                if ($list['status']!=2 && $list['status']!=4){
                    $re = Db::name('fd_withdrawal')->where('order_id', $info['order_id'])->setField('status',5);
                }
            }
            if ($re == true) {
                return "SUCCESS";
            } else {
                return "ERROR";
            }
        } else {
            return "ERROR";
        }


    }

}