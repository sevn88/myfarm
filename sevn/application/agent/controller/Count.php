<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/1
 * Time: 23:17
 * QQ:1467572213
 */

namespace app\agent\controller;

use think\facade\Request;
use think\facade\Session;
use \app\agent\model\Count as Count_m;

class Count extends Base
{
    public function statistics()
    {


        $start_date = input('get.start_date');
        $end_date = input('get.end_date');

        if (!$start_date) {
            $start_date = date('Y-m-d', time());
        }
        if (!$end_date) {
            $end_date = date('Y-m-d', time());
        }

        $day = input('get.day');
        if (!$day) {
            $day = -5;
        }
        $this->assign('day', $day);
        $this->assign('start_date', $start_date);
        $this->assign('end_date',$end_date);

        //昨天
        $yes1 = strtotime(date("Y-m-d 00:00:00", strtotime($start_date)));
        $yes2 = strtotime(date("Y-m-d 23:59:59", strtotime($end_date)));
        $this->assign('yes1', $yes1);
        $this->assign('yes2', $yes2);

        $agent_id = Session::get("agent_id");
        if ($agent_id){

            $info = Count_m::statistics($agent_id,$start_date,$end_date);

            $this->assign('info',$info);
            // $list = Count_m::recharge_list($agent_id,$start_date,$end_date);
            // $this->assign('list',$list);

        }else{
            $info = null;
            $this->assign('info',$info);
            // $this->assign('list',[]);
        }

        return $this->fetch();
    }


public function detail()
    {


        $start_date = input('get.start_date');
        $end_date = input('get.end_date');

        if (!$start_date) {
            $start_date = date('Y-m-d', time());
        }
        if (!$end_date) {
            $end_date = date('Y-m-d', time());
        }

        $day = input('get.day');
        if (!$day) {
            $day = -5;
        }
        $this->assign('day', $day);
        $this->assign('start_date', $start_date);
        $this->assign('end_date',$end_date);

        //昨天
        $yes1 = strtotime(date("Y-m-d 00:00:00", strtotime($start_date)));
        $yes2 = strtotime(date("Y-m-d 23:59:59", strtotime($end_date)));
        $this->assign('yes1', $yes1);
        $this->assign('yes2', $yes2);

        $agent_id = Session::get("agent_id");
        if ($agent_id){

            $info = Count_m::statistics($agent_id,$start_date,$end_date);

            $this->assign('info',$info);
            $list = Count_m::recharge_list($agent_id,$start_date,$end_date);
            $this->assign('list',$list);

        }else{
            $info = null;
            $this->assign('info',$info);
            $this->assign('list',[]);
        }

        return $this->fetch();
    }
    public function report()
    {
        return $this->fetch();
    }

}