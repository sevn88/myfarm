<?php
namespace app\api\controller;//注意写准确命名空间
use think\Controller;
class UserController extends Controller //类名字 和控制器名字一样
{
    public function read()
    {
        $uid = input('uid');
        $model = model('User');
        $data = $model->getUsers($uid);// 查询数据---模型下的方法
        if ($data) {
            $code = 200;
        } else {
            $code = 404;
        }
        $data = [
            'code' => $code,
            'data' => $data
        ];
        return json($data);
    }
}