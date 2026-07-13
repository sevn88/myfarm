<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/28
 * Time: 10:58
 * QQ:1467572213
 */

namespace app\admin\model;

use app\index\model\Base;
use think\Model;
use think\Db;
use think\facade\Config;
use think\facade\Session;

class Order extends Model
{
    public static function index()
    {
        $keyword = input('get.keyword/s','');

        $usertel = input('get.usertel/s','');
        $userid = input('get.userid/s','');
        $where=array();

        if ($keyword || $usertel || $userid) {
            $where = array();
            if ($keyword) {

                $where[] = array('username', "like", "%$keyword%");
            }
            if ($usertel) {

                $where[] = array("phone", 'like', "%$usertel%");
            }
            if ($userid) {

                $where[] = array('id', 'like', "%$userid%");
            }
        }
//        $where[]=array('auto_grab','=','1');

        $auto_grab = Db::name('fd_user')->where($where)
            ->paginate(10,false,['query'=>request()->param()]);

        return $auto_grab;
    }

    public static function push_order($keyword)
    {
        if ($keyword){
            $goods_list = Db::name('fd_goods_list')
                ->where('goods_name',$keyword)
                ->paginate(10,false,['query'=>request()->param()]);
        }else{
            $goods_list = Db::name('fd_goods_list')
                ->order('id','desc')
                ->paginate(10,false,['query'=>request()->param()]);
        }
        return $goods_list;
    }

    public static function push_goods($uid,$id)
    {
        $user_info = Base::get_user_info($uid);
        $goods_info = Goods::goods_info($id);
        $push_goods['order_id'] = 'A'.date('md').time().mt_rand(111,999);
        $push_goods['uid'] = $user_info['id'];
        $push_goods['username'] = $user_info['username'];
        $push_goods['phone'] = $user_info['phone'];
        $push_goods['goods_id'] = $goods_info['id'];
        $push_goods['goods_name'] = $goods_info['goods_name'];
        // $push_goods['goods_info'] = $goods_info['goods_info'];
        $push_goods['goods_price'] = $goods_info['goods_price'];
        $push_goods['goods_pic'] = $goods_info['goods_pic'];
        $push_goods['goods_type'] = $goods_info['type'];
        $push_goods['order_commission'] = Db::name('fd_goods_type')->where('id',$goods_info['type'])->value('bili');
        $push_goods['order_earnings'] = $goods_info['goods_price'] * $push_goods['order_commission'];
        $push_goods['status'] = 0;
        $push_goods['create_time'] = time();
        $add_push = Db::name('fd_order')->insert($push_goods);
        if ($add_push == true){
            $stop_grab = Db::name('fd_user')->where('id',$uid)->setField('auto_grab',0);
            if ($stop_grab == true){
                return 'success';
            }else{
                return 'error_stop';
            }
        }else{
            return 'error';
        }
    }

    public static function order_list($keyword,$userid,$userphone)
    {

        if ($keyword || $userid || $userphone){
            $where = array();
            if ($keyword){
                $where[]=array('username',"like","%$keyword%");
            }
            if ($userphone){

                $where[]=array("phone",'like',"%$userphone%");
            }
            if ($userid){

                $where[]=array('uid','like',"%$userid%");
            }

            $list = Db::name('fd_order')
                ->where($where)
                ->order('id','desc')
                ->paginate(10,false,['query'=>request()->param()])->each(function($item,$key){
                    $item["p_user_balance"]=Db::name('fd_user')->where('id',$item["uid"])->field('balance')->value('balance');
                    return $item;});
            return $list;
        }else{
            $list = Db::name('fd_order')
                ->order('id','desc')
                ->paginate(10,false,['query'=>request()->param()])->each(function($item,$key){
                    $item["p_user_balance"]=Db::name('fd_user')->where('id',$item["uid"])->field('balance')->value('balance');
                    return $item;});
            return $list;
        }
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

    public static function group_list($keyword)
    {
        if ($keyword){
            $list = Db::name('fd_group')
                ->where('name',$keyword)
                ->paginate(10,false,['query'=>request()->param()]);
            return $list;
        }else{
            $list = Db::name('fd_group')
                ->order('id','desc')
                ->paginate(10,false,['query'=>request()->param()]);
            return $list;
        }
    }

    public static function group_add($info)
    {
        $group_add = Db::name('fd_group')->insert($info);
        if ($group_add == true){
            return 'success';
        }else{
            return 'error';
        }
        die;
        $where['id'] = $info['aid'];
        $check_aid = Db::name('fd_user')->where('id',$info['aid'])->find();
        if ($check_aid){
            if ($check_aid['role'] == 2){
                $group_add = Db::name('fd_group')->insert($info);
                if ($group_add == true){
                    return 'success';
                }else{
                    return 'error';
                }
            }else{
                return 'error_role';
            }
        }else{
            return 'not_user';
        }
    }

    public static function group_info($id)
    {
        $info = Db::name('fd_group')->where('id',$id)->find();
        return $info;
    }

    public static function group_edit($group_id,$info)
    {
        $group_edit = Db::name('fd_group')->where('id',$group_id)->update($info);
        if ($group_edit == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function group_user($group_id,$type,$user_id)
    {
        Db::name('fd_user')->where('id',$user_id)->setField('group_order',0);
        switch ($type)
        {
            case 0:
                $where['id'] = $user_id;
                Db::name('fd_user')->where($where)->setField('group',$group_id);
                break;
            case 1:
                $where['id'] = $user_id;
                $where['group'] = $group_id;
                Db::name('fd_user')->where($where)->setField('group',0);
                break;
        }
        return 'success';
    }

    public static function get_pid_z()
    {
        $get_pid_z = Db::name('fd_user')->select();
        return $get_pid_z;
    }

    public static function get_pid_y($group_id)
    {
        $where['group'] = $group_id;
        $get_pid_y = Db::name('fd_user')->where($where)->select();
        return $get_pid_y;
    }

    public static function add_user($agent_id,$group_id,$type,$user_id)
    {
        Db::name('fd_user')->where('id',$user_id)->setField('group_order',0);
        switch ($type)
        {
            case 0:
                $where['id'] = $user_id;
                $where['pid'] = $agent_id;
                Db::name('fd_user')->where($where)->setField('group',$group_id);
                break;
            case 1:
                $where['id'] = $user_id;
                $where['pid'] = $agent_id;
                $where['group'] = $group_id;
                Db::name('fd_user')->where($where)->setField('group',0);
                break;
        }
        return 'success';
    }

    public static function level_info()
    {
        $level_info = Db::name('fd_level')->select();
        return $level_info;
    }

    public static function group_select()
    {
        $my_group = Db::name('fd_group')->select();
        return $my_group;
    }

    public static function group_mode($id)
    {
        $group_mode = Db::name('fd_group_mode')->where('group_id',$id)->select();
        return $group_mode;
    }

    public static function mode_save($info)
    {
        $mode_save = Db::name('fd_group_mode')->insert($info);
        if ($mode_save == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function del_mode($id)
    {
        $where['id'] = $id;
        $del_mode = Db::name('fd_group_mode')->where($where)->delete();
        if ($del_mode == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function group_del($id)
    {
        $check_group = Db::name('fd_user')->where('group',$id)->count();
        if ($check_group == 0){
            $where['id'] = $id;
            $group_del = Db::name('fd_group')->where($where)->delete();
            if ($group_del == true){
                return 'success';
            }else{
                return 'error';
            }
        }else{
            return 'error_ues';
        }
    }

    public static function pop_pic_list($keyword)
    {
        if ($keyword){
            $list = Db::name('fd_pop')
                ->where('status',1)
                ->where('name',$keyword)
                ->paginate(10,false,['query'=>request()->param()]);
        }else{
            $list = Db::name('fd_pop')
                ->where('status',1)
                ->paginate(10,false,['query'=>request()->param()]);
        }
        return $list;
    }

    public static function pop_pic_add($info)
    {
        $add = Db::name('fd_pop')->insert($info);
        if ($add == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function pop_pic_del($id)
    {
        $del = Db::name('fd_pop')->where('id',$id)->delete();
        if ($del == true){
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

    public static function order_add_run($oid,$goods_id)
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

    public static function order_frozen($id)
    {
       $frozen =  Db::name('fd_order')->where('id',$id)->setField('status',3);
       if ($frozen == true){
           return 'success';
       }else{
           return 'error';
       }
    }

}