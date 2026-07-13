<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/2
 * Time: 16:59
 * QQ:1467572213
 */

namespace app\agent\controller;

use think\facade\Request;
use think\facade\Session;
use \app\agent\model\Group as Group_m;

class Group extends Base
{
    public function index()
    {
        $agent_id = Session::get('agent_id');
        $list = Group_m::index($agent_id);
        $page = $list->render();
        $this->assign('pages', $page);
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function add_group()
    {
        $agent_id = Session::get('agent_id');
        if (Request::isPost()) {
            $info['aid'] = $agent_id;
            $info['name'] = Request::post('name');
            $pay = Request::post('pay');
            if ($pay) {
                $info['pay'] = implode(',', $pay);
            }
            $info['create_time'] = time();
            $add = Group_m::add_group($info);
            return $this->jsonResult($add, '添加成功', '添加失败');
        }
        return $this->fetch();
    }

//    public function add_mode($id)
//    {
//        $agent_id = Session::get('agent_id');
//        $level_info = Group_m::level_info();
//        $this->assign('level_info',$level_info);
//        $my_group = Group_m::get_my_group($agent_id);
//        $this->assign('my_group',$my_group);
//        if ($id){
//            $group_info = Group_m::get_group_info($agent_id,$id);
//            $this->assign('group_info',$group_info);
//            $group_mode = Group_m::group_mode($id);
//            if ($group_mode){
//                $this->assign('group_mode',$group_mode);
//            }
//        }else{
//            $this->error('参数错误');
//        }
//        return $this->fetch();
//    }

    public function mode_save()
    {
        $agent_id = Session::get('agent_id');
        $level_info = Group_m::level_info();
        $this->assign('level_info',$level_info);
        $my_group = Group_m::get_my_group($agent_id);
        $this->assign('my_group',$my_group);
        if (Request::isPost()){
            $mode['aid'] = $agent_id;
            $mode['group_id'] = Request::post('group_id');
            $mode['grab_type'] = Request::post('grab_type');
            $mode['is_windows'] = Request::post('is_windows');
            if (!$mode['is_windows']) $mode['is_windows'] = 0;
            $mode['windows_img'] = Request::post('windows_img');
            $mode['is_level'] = Request::post('is_level');
            if (!$mode['is_level']) $mode['is_level'] = 0;
            $mode['level'] = Request::post('level');
            $mode['is_group'] = Request::post('is_group');
            if (!$mode['is_group']) $mode['is_group'] = 0;
            $mode['group'] = Request::post('group');
            $mode['odd_num'] = Request::post('odd_num');
            $mode['pay_mode'] = Request::post('pay_mode');
            $mode['pay_value'] = Request::post('pay_value');
            $mode['rand_num'] = Request::post('rand_num');
            $mode['rand_bili'] = Request::post('rand_bili');
            $mode['addition'] = Request::post('addition');
            $mode['create_time'] = time();
            $mode_save = Group_m::mode_save($mode);
            return $this->jsonResult($mode_save, '保存成功', '保存失败');
        }
    }

    public function get_pid()
    {
        if (Request::isPost()){
            $agent_id = Session::get('agent_id');
            $group_id = Request::post('group_id');
            $get_pid_z = Group_m::get_pid_z($agent_id);
            $z = [];
            foreach ($get_pid_z as $k => $v){
                $z[] = [
                    'value'=>$v['id'],
                    'title'=>$v['username']
                ];
            }
            $get_pid_y = Group_m::get_pid_y($agent_id,$group_id);

            $y = [];
            foreach ($get_pid_y as $k => $v){
                $y[] = [
                    $v['id'],
                ];
            }
            return ['z'=>$z,'y'=>$y];
        }
    }

    public function add_user()
    {
        $agent_id = Session::get('agent_id');
        if (Request::isPost()){
            $group_id = Request::post('group_id');
            $type = Request::post('type');
            $data = json_decode(Request::post('data'),true);
            foreach ($data as $k => $v){
                $add_user = Group_m::add_user($agent_id,$group_id,$type,$v['value']);
            }
            if ($add_user == 'success')
                return ['code'=>1,'msg'=>'修改成功'];

        }

        $id = Request::param();
        if ($id){
            $this->assign('id',$id[0]);
            $group_info = Group_m::get_group_info($agent_id,$id[0]);
            $this->assign('group_info',$group_info);
        }else{
            $this->error('参数错误');
        }
        return $this->fetch();
    }

//    public function edit($id)
//    {
//        $agent_id = Session::get('agent_id');
//        $group_info = Group_m::get_group_info($agent_id,$id);
//        $this->assign('group_info',$group_info);
//        $level_info = Group_m::level_info();
//        $this->assign('level_info',$level_info);
//        $my_group = Group_m::get_my_group($agent_id);
//        $this->assign('my_group',$my_group);
//        if (Request::isPost()){
//            $info['aid'] = $agent_id;
//            $info['name'] = Request::post('name');
//            $pay = Request::post('pay');
//            if ($pay){
//                $info['pay'] = implode(',',$pay);
//            }
//            $info['grab_type'] = Request::post('grab_type');
//            $info['update_time'] = time();
//            $edit = Group_m::edit($id,$agent_id,$info);
//            switch ($edit)
//            {
//                case 'success':
//                    $this->success('修改成功');
//                    break;
//                case 'error':
//                    $this->error('修改失败');
//                    break;
//            }
//        }
//        return $this->fetch();
//    }

//    public function group_del()
//    {
//        $agent_id = Session::get('agent_id');
//        if (Request::isPost()){
//            $id = Request::post('id');
//            $group_del = Group_m::group_del($agent_id,$id);
//            switch ($group_del)
//            {
//                case 'success':
//                    return ['code'=>1,'msg'=>'删除成功'];
//                    break;
//                case 'error':
//                    return ['code'=>0,'msg'=>'删除失败'];
//                    break;
//                case 'error_ues':
//                    return ['code'=>0,'msg'=>'还有用户在该分组,请清除后再删除.'];
//                    break;
//            }
//        }
//    }

//    public function del_mode()
//    {
//        $agent_id = Session::get('agent_id');
//        if (Request::isPost()){
//            $id = Request::post('id');
//            $del_mode = Group_m::del_mode($agent_id,$id);
//            switch ($del_mode)
//            {
//                case 'success':
//                    return ['code'=>1,'msg'=>'删除成功'];
//                    break;
//                case 'error':
//                    return ['code'=>0,'msg'=>'删除失败'];
//                    break;
//            }
//        }
//    }


}