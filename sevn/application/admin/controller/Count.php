<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/10/9
 * Time: 22:26
 * QQ:1467572213
 */

namespace app\admin\controller;

use library\Controller;
use think\facade\Request;
use think\facade\Session;
use think\facade\Cache;
use \app\admin\model\Count as Count_m;

use think\Model;
use think\Db;
use think\model\Collection as ModelCollection;
use think\Db\Query;


/**
 * 代理统计
 * Class Goods
 * @package app\admin\controller
 */
class Count extends Controller
{
    /**
     * 代理统计
     *@auth true
     *@menu true
     */
    public function index()
    {
        
        $http = 'admin.html#' . $this->request->url();
//        $http = preg_replace("/" . preg_quote("&day", "/") . ".*/si", "", $http);
        $this->assign('http', $http);

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
//        $info = Index_m::statistics($yes1, $yes2);
//        $this->assign('info', $info);
        $pid_name = input('get.pid_name');
        $action=input('get.action');
        $this->assign('pid_name',$pid_name);
        $default_info = [
            'pid_name'      => '',
            'reg_num'       => 0,
            'c_recharge'    => 0,
            'service_cz'    => 0,
            'm_recharge'    => 0,
            'm_withdrawal'  => 0,
            'm_withdrawal_cost_new' => 0,
            'withdrawal_number' => 0,
            'shouchong'     => 0,
            'profit_rate'   => 0,
            'profit'        => 0,
        ];
        if ($action){
            $info=Count_m::index($start_date,$end_date,$pid_name);
            $this->assign('list',$info);
        }else{
            $this->assign('list',[]);
        }
        $this->assign('info',$default_info);
        
//         if ($pid_name){
//             $info = Count_m::index($start_date,$end_date,$pid_name);
//             if ($info==[]){
//                 $info=null;    
//             }
            
//             $this->assign('info',$info);
//             // $list = Count_m::recharge_list($start_date,$end_date,$pid_name);
//             // $this->assign('list',$list);
// //            $page = $list->render();  //构造分页
// //            $this->assign('pages', $page);   //输出分页
//         }else{
//             $info = null;
//             $this->assign('info',$info);
//             // $this->assign('list',[]);
//         }
        $recharge_type = Count_m::recharge_type();
        $this->assign("recharge_type",$recharge_type);

        $this->fetch();

    }
    
    
     public function detail()
    {
        
        $http = 'admin.html#' . $this->request->url();
//        $http = preg_replace("/" . preg_quote("&day", "/") . ".*/si", "", $http);
        $this->assign('http', $http);

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
//        $info = Index_m::statistics($yes1, $yes2);
//        $this->assign('info', $info);
        $pid_name = input('get.pid_name');
        $this->assign('pid_name',$pid_name);
        if ($pid_name){
            $info = Count_m::index_detail($start_date,$end_date,$pid_name);
            if ($info==[]){
                $info = [
                    'pid_name'      => $pid_name,
                    'reg_num'       => 0,
                    'c_recharge'    => 0,
                    'service_cz'    => 0,
                    'm_recharge'    => 0,
                    'm_withdrawal'  => 0,
                    'm_withdrawal_cost_new' => 0,
                    'withdrawal_number' => 0,
                    'shouchong'     => 0,
                    'profit_rate'   => 0,
                    'profit'        => 0,
                ];
            }
            $this->assign('info',$info);
            
            $list = Count_m::recharge_list($start_date,$end_date,$pid_name);
            if ($list==[]){
                $this->assign('list',[]);
            }
            else{
                $this->assign('list',$list);
            }
            
//            $page = $list->render();  //构造分页
//            $this->assign('pages', $page);   //输出分页
        }else{
            $default_info = [
                'pid_name'      => '',
                'reg_num'       => 0,
                'c_recharge'    => 0,
                'service_cz'    => 0,
                'm_recharge'    => 0,
                'm_withdrawal'  => 0,
                'm_withdrawal_cost_new' => 0,
                'withdrawal_number' => 0,
                'shouchong'     => 0,
                'profit_rate'   => 0,
                'profit'        => 0,
            ];
            $this->assign('info',$default_info);
            $this->assign('list',[]);
        }
        $recharge_type = Count_m::recharge_type();
        $this->assign("recharge_type",$recharge_type);

        $this->fetch();

    }
    
    
}