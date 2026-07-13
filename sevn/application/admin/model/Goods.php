<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/8/25
 * Time: 21:49
 * QQ:1467572213
 */

namespace app\admin\model;

use think\Model;
use think\Db;
use think\facade\Config;
use think\facade\Session;

class Goods extends Model
{
    public static function goods_list($keyword)
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

    public static function goods_info($id)
    {
        $goods_info = Db::name('fd_goods_list')->where('id',$id)->find();
        return $goods_info;
    }

    public static function goods_add($goods_info)
    {
        $goods_add = Db::name('fd_goods_list')->insert($goods_info);
        if ($goods_add == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function goods_edit($id,$edit_info)
    {
        $goods_edit = Db::name('fd_goods_list')->where('id',$id)->update($edit_info);
        if ($goods_edit == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function goods_type()
    {
        $goods_type = Db::name('fd_goods_type')->select();
        return $goods_type;
    }

    public static function goods_type_add($goods_info)
    {
        $goods_type_add = Db::name('fd_goods_type')->insert($goods_info);
        if ($goods_type_add == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function goods_type_info($id)
    {
        $goods_type_info = Db::name('fd_goods_type')->where('id',$id)->find();
        return $goods_type_info;
    }

    public static function goods_type_edit($id,$edit_info)
    {
        $goods_type_edit = Db::name('fd_goods_type')->where('id',$id)->update($edit_info);
        if ($goods_type_edit == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function goods_type_del($id)
    {
        $del = Db::name('fd_goods_type')->where('id',$id)->delete();
        if ($del == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function roll_list()
    {
        $list = Db::name('fd_roll')->where('type',1)->order('id','desc')
            ->paginate(10,false,['query'=>request()->param()]);
        return $list;
    }

    public static function roll_add($info)
    {
        $roll_add = Db::name('fd_roll')->insert($info);
        if ($roll_add == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function roll_del($id)
    {
        $roll_del = Db::name('fd_roll')->where('id',$id)->delete();
        if ($roll_del == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function news_list()
    {
        $list = Db::name('fd_roll')->where('type',2)->order('id','desc')
            ->paginate(10,false,['query'=>request()->param()]);
        return $list;
    }

    public static function news_add($info)
    {
        $news_add = Db::name('fd_roll')->insert($info);
        if ($news_add == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function news_del($id)
    {
        $news_del = Db::name('fd_roll')->where('id',$id)->delete();
        if ($news_del == true){
            return 'success';
        }else{
            return 'error';
        }
    }

}