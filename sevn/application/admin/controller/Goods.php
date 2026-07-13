<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller;

use library\Controller;
use think\facade\Request;
use think\facade\Session;
use \app\admin\model\Goods as Goods_m;

/**
 * 商品管理
 * Class Goods
 * @package app\admin\controller
 */
class Goods extends Controller
{
    /**
     * 商品列表
     *@auth true
     *@menu true
     */
    public function goods_list()
    {
        $this->title = '商品列表';
        $keyword = input('get.keyword/s','');
        $list = Goods_m::goods_list($keyword);
        $page = $list->render();
        $this->assign('pages', $page);
        $this->assign('list', $list);
        $goods_type = Goods_m::goods_type();
        $this->assign('goods_type', $goods_type);
        return $this->fetch();
    }

    /**
     * 添加商品
     *@auth true
     */
    public function goods_add()
    {
        if (Request::isPost()){
            $goods_info = [
                'goods_name' => Request::post('goods_name'),
                'goods_price' => Request::post('goods_price'),
                'goods_pic' => Request::post('goods_pic'),
                'type' => Request::post('type'),
                'status' => 1,
                'create_time' => time(),
            ];
            $goods_add = Goods_m::goods_add($goods_info);
            return $this->jsonResult($goods_add, '添加成功', '添加失败');
        }
        $goods_type = Goods_m::goods_type();
        $this->assign('goods_type', $goods_type);
        return $this->fetch();
    }

    /**
     * 编辑商品
     *@auth true
     */
    public function goods_edit($id)
    {
        $id = (int)$id;
        $goods_info = Goods_m::goods_info($id);
        $this->assign('goods_info', $goods_info);
        $goods_type = Goods_m::goods_type();
        $this->assign('goods_type', $goods_type);
        if (Request::isPost()){
            $edit_info = [
                'goods_name' => Request::post('goods_name'),
                'type' => Request::post('type'),
                'goods_price' => Request::post('goods_price'),
                'goods_pic' => Request::post('goods_pic'),
                'goods_info' => Request::post('goods_info'),
                'update_time' => Request::post('update_time'),
            ];
            $edit_info = Goods_m::goods_edit($id, $edit_info);
            return $this->jsonResult($edit_info, '修改成功', '修改失败');
        }
        return $this->fetch();
    }

    /**
     * 更改商品状态
     * @auth true
     */
    public function edit_goods_status()
    {
        $this->_form('xy_goods_list', 'form');
    }

    /**
     * 删除商品
     * @auth true
     */
    public function goods_del()
    {
        $this->_delete('fd_goods_list');
    }

    /**
     * 商品分类
     *@auth true
     */
    public function goods_type()
    {
        $this->title = '商品分类';
        $this->_query('fd_goods_type')->page();
    }

    /**
     * 添加分类
     *@auth true
     */
    public function goods_type_add()
    {
        if (Request::isPost()){
            $goods_type_info = [
                'name' => Request::post('name'),
                'bili' => Request::post('bili'),
                'cate_info' => Request::post('cate_info'),
                'min' => Request::post('min'),
                'status' => 1,
                'create_time' => time(),
            ];
            $goods_type_add = Goods_m::goods_type_add($goods_type_info);
            return $this->jsonResult($goods_type_add, '添加成功', '添加失败');
        }
        return $this->fetch();
    }

    /**
     * 编辑商品分类
     * @auth true
     */
    public function goods_type_edit($id)
    {
        $id = (int)$id;
        $goods_type_info = Goods_m::goods_type_info($id);
        $this->assign('info', $goods_type_info);
        if (Request::isPost()){
            $edit_info = [
                'name' => Request::post('name'),
                'bili' => Request::post('bili'),
                'cate_info' => Request::post('cate_info'),
                'min' => Request::post('min'),
                'update_time' => time(),
            ];
            $goods_type_edit = Goods_m::goods_type_edit($id, $edit_info);
            return $this->jsonResult($goods_type_edit, '修改成功', '修改失败');
        }
        return $this->fetch();
    }

    /**
     * 删除商品分类
     * @auth true
     */
    public function goods_type_del()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $del = Goods_m::goods_type_del($id);
            return $this->jsonResult($del, '删除成功', '删除失败');
        }
    }

    /**
     * 首页Roll列表
     *@auth true
     */
    public function roll_list()
    {
        $this->title = '首页Roll';
        $list = Goods_m::roll_list();
        $page = $list->render();
        $this->assign('pages', $page);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加Roll
     *@auth true
     */
    public function roll_add()
    {
        if (Request::isPost()){
            $info = [
                'name' => Request::post('name'),
                'type' => 1,
                'sign' => Request::post('sign'),
                'img' => Request::post('img'),
                'money' => Request::post('money'),
                'status' => 1,
                'create_time' => time(),
            ];
            $roll_add = Goods_m::roll_add($info);
            return $this->jsonResult($roll_add, '添加成功', '添加失败');
        }
        return $this->fetch();
    }

    /**
     * 删除Roll
     *@auth true
     */
    public function roll_del()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $del = Goods_m::roll_del($id);
            return $this->jsonResult($del, '删除成功', '删除失败');
        }
    }

    /**
     * 首页News列表
     *@auth true
     */
    public function news_list()
    {
        $this->title = '首页News';
        $list = Goods_m::news_list();
        $page = $list->render();
        $this->assign('pages', $page);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加News
     *@auth true
     */
    public function news_add()
    {
        if (Request::isPost()){
            $info['name'] = Request::post('name');
            if (empty($info['name'])) $this->error('昵称不能为空');
            if (strlen($info['name']) < 6) $this->error('昵称长度至少6位');
            $info = [
                'name' => Request::post('name'),
                'type' => 2,
                'sign' => 0,
                'img' => Request::post('img'),
                'money' => Request::post('money'),
                'status' => 1,
                'create_time' => time(),
            ];
            $roll_add = Goods_m::news_add($info);
            return $this->jsonResult($roll_add, '添加成功', '添加失败');
        }
        return $this->fetch();
    }

    /**
     * 删除News
     *@auth true
     */
    public function news_del()
    {
        if (Request::isPost()){
            $id = Request::post('id');
            $del = Goods_m::news_del($id);
            return $this->jsonResult($del, '删除成功', '删除失败');
        }
    }
}
