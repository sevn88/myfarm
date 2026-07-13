<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/2
 * Time: 16:59
 * QQ:1467572213
 */

namespace app\agent\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Group extends Model
{
    public static function index($agent_id)
    {
        $list = Db::name('fd_group')->order('id', 'desc')
            ->paginate(10, false, ['query' => request()->param()]);
        return $list;
    }

    public static function level_info()
    {
        $level_info = Db::name('fd_level')->select();
        return $level_info;
    }

    public static function get_my_group($agent_id)
    {
        $my_group = Db::name('fd_group')->where('aid',$agent_id)->select();
        return $my_group;
    }

    public static function add_group($info)
    {
        $add_easy = Db::name('fd_group')->insert($info);
        if ($add_easy == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function get_pid_z($agent_id)
    {
        $where['pid'] = $agent_id;
        $get_pid_z = Db::name('fd_user')->where($where)->select();
        return $get_pid_z;
    }

    public static function get_pid_y($agent_id,$group_id)
    {
        $where['pid'] = $agent_id;
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
                $map = [
                    ['uid', '=', $user_id],
                    ['status', 'in', [0,2,3]],
                ];
                $user_order = Db::name('fd_order')->where($map)->select();
                foreach ($user_order as $k => $v){
                    Db::name('fd_order')->where('id',$v['id'])->setField('status',4);
                }
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

    public static function get_group_info($agent_id,$id)
    {
        $where['aid'] = $agent_id;
        $where['id'] = $id;
        $group_info = Db::name('fd_group')->where($where)->find();
        return $group_info;
    }

    public static function edit($id,$agent_id,$info)
    {
        $where['id'] = $id;
        $where['aid'] = $agent_id;
        $edit = Db::name('fd_group')->where($where)->update($info);
        if ($edit == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function group_del($agent_id,$id)
    {
        $check_group = Db::name('fd_user')->where('group',$id)->count();
        if ($check_group == 0){
            $where['id'] = $id;
            $where['aid'] = $agent_id;
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

    public static function mode_save($mode)
    {
        $mode_save = Db::name('fd_group_mode')->insert($mode);
        if ($mode_save == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function group_mode($id)
    {
        $group_mode = Db::name('fd_group_mode')->where('group_id',$id)->select();
        return $group_mode;
    }

    public static function del_mode($agent_id,$id)
    {
        $where['id'] = $id;
        $where['aid'] = $agent_id;
        $del_mode = Db::name('fd_group_mode')->where($where)->delete();
        if ($del_mode == true){
            return 'success';
        }else{
            return 'error';
        }
    }

}