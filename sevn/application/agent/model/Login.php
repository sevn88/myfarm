<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/9/1
 * Time: 22:31
 * QQ:1467572213
 */

namespace app\agent\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Login extends Model
{
    public static function index($username,$password)
    {
        $where['username'] = $username;
        // $where['role'] = 2;
        $where['status'] = 1;
        $where['password']=md5($password);
        $check_user = Db::name('fd_user')->where($where)->find();
        if ($check_user){
            if (strval($check_user['role'])=='2' || strval($check_user['role'])=='3' || strval($check_user['role'])=='4'){
                return $check_user;
            }else{
                return 'error';
            }
        }else{
            return 'error';
        }
// //        var_dump(Db::getLastSql());die;
//         if ($check_user){
//             if ($check_user['password'] == md5($password)){
//                 return $check_user;
//             }else{
//                 return 'error';
//             }
//         }else{
//             $where['role']=3;
//             $check_user= Db::name('fd_user')->where($where)->find();
//             if ($check_user){
//                 if ($check_user['password']==md5($password)){
//                     return $check_user;
//                 }else{
//                     return 'error';
//                 }
//             }else{
                
//             }
            
//             return 'error';
//         }
    }

}