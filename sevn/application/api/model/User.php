<?php
namespace app\api\model;

use think\Model;
use think\Db;
class User extends Model{
    public function getUsers($uid = 1){
        $res = Db::name('fd_user')->where('id', $uid)->select();
        // echo $this->getLastSql();
        return $res;
    }
}