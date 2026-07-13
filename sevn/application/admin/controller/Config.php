<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | www.soku.cc搜库资源网 
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 

// +----------------------------------------------------------------------

namespace app\admin\controller;

use library\Controller;
use think\Db;
use think\exception\HttpResponseException;
use think\facade\Request;
use \app\index\model\Base as IndexBase;

/**
 * 系统参数配置
 * Class Config
 * @package app\admin\controller
 */
class Config extends Controller
{
    /**
     * 默认数据模型
     * @var string
     */
    protected $table = 'SystemConfig';

    /**
     * 系统参数配置
     * @auth true
     * @menu true
     */
    public function info()
    {
        $this->title = '系统参数配置';
        $this->info=[];
        $this->fetch();
    }
    
    /**
     *  删除僵尸数据
     * 
     */
    public function clear1(){
        if (Request::isPost()){
            $days = (int)Request::post("id");
            $timestamp = strtotime('-'.$days.' days');
            $bonus = floatval(Base::get_config("zhuce_zengsong"));
            $ret = Db::name("fd_user")
                ->where("balance", $bonus)
                ->where("role", 1)
                ->where("last_time", "<", $timestamp)
                ->delete();
            return $ret;
        }
    }
     /**
     *  删除僵尸数据 用户等级为1，余额没有发生变化的，指定日期之前的数据
     * 
     */
    public function clear2(){
        if (Request::isPost()){
            $days = (int)Request::post('id');
            
            if (!empty($days)){
                $timestamp = strtotime($days);
                
                // $timestamp = strtotime('-'.strval($days).' days');
                // $sql = "delete from fd_user where balance=".strval(Base::get_config('zhuce_zengsong'))." and role=1 and last_time<".strval($timestamp);
                // $where=array();
                // $where[]=array("role",'=','1');
                // $where[]=array("balance",'=',Base::get_config('zhuce_zengsong'));
                // $where[]=array("last_time","<",$timestamp);
                // $res = Db::name("fd_user")->where('role',1)->where('balance',Base::get_config('zhuce_zengsong'))->where('last_time','<',$timestamp)->field("id")->select();
                $res = Db::name("fd_user")->where('role',1)->where('balance',Base::get_config('zhuce_zengsong'))->where('last_time','<',$timestamp)->column("id");

                $ids=$res;
                $res2 = Db::name("fd_user")->where('id','in',$ids)->delete();
                Db::name("fd_order")->where('uid','in',$ids)->delete();
                Db::name("fd_recharge")->where('uid','in',$ids)->delete();
                Db::name("fd_withdrawal")->where('uid','in',$ids)->delete();

                return $res2;    
            }
            
            
            
        }
        
        
    }
     /**
     *  删除僵尸数据 用户等级为1，余额没有发生变化的，指定日期之前的数据
     * 
     */
    public function clear3(){
        if (Request::isPost()){
            $days = (int)Request::post('id');
            
            if (!empty($days)){
                $timestamp = strtotime($days);
                
                // $timestamp = strtotime('-'.strval($days).' days');
                $sql = "delete from fd_user where  role=1 and last_time<".strval($timestamp);
                $res = Db::name("fd_user")->where('role',1)->where('last_time','<',$timestamp)->column("id");

                $ids=$res;
                $res2 = Db::name("fd_user")->where('id','in',$ids)->delete();
                Db::name("fd_order")->where('id','in',$ids)->delete();
                Db::name("fd_recharge")->where('id','in',$ids)->delete();
                Db::name("fd_withdrawal")->where('id','in',$ids)->delete();

                return $res2;    
            }
            
            
            
        }
        
        
    }
    /**
     *  删除僵尸数据 用户等级为1，余额没有发生变化的，指定日期之前的数据
     * 
     */
    public function clear4(){
        if (Request::isPost()){
            $days = (int)Request::post("id");
            if (!empty($days)){
                $timestamp = strtotime($days);
                $ids = Db::name("fd_recharge")
                    ->where("status", "<>", 1)
                    ->where("create_time", "<", $timestamp)
                    ->column("id");
                if (!empty($ids)) {
                    $ret = Db::name("fd_recharge")
                        ->where("id", "in", $ids)
                        ->delete();
                    return $ret;
                }
            }
        }
    }
    /**
     *  删除僵尸数据 用户等级为1，余额没有发生变化的，指定日期之前的数据
     * 
     */
    public function clear5(){
        if (Request::isPost()){
            $days = (int)Request::post('id');
            
            if (!empty($days)){
                $timestamp = strtotime($days);
                
                // $timestamp = strtotime('-'.strval($days).' days');
                $sql = "delete from fd_recharge  where  status=1 and create_time<".strval($timestamp);
                $ids = Db::name("fd_recharge")->where('status','1')->where('create_time','<',$timestamp)->column("id");
                $ret = Db::name("fd_recharge")->where('id','in',$ids)->delete();
                return $ret;    
            }
            
            
            
        }
        
        
    }
    /**
     *  删除传过来的代理的信息以及它下面的子信息
     * 
     */
    public function clear6(){
        if (Request::isPost()){
            $username = Request::post('id');
            
            if (!empty($username)){
                
                $res = Db::name("fd_user")->where("username",$username)->column("id");
                $res_new = self::get_ids_clear6($res);
                $res_new = array_merge($res,$res_new);

                $deleted = Db::name("fd_user")->where('id','in',$res_new)->delete();
                Db::name("fd_order")->where('uid','in',$res_new)->delete();
                Db::name("fd_recharge")->where('uid','in',$res_new)->delete();
                Db::name("fd_withdrawal")->where('uid','in',$res_new)->delete();
                return "删除了".$deleted."条用户信息";
                 
            }
            else{
                return "输入具体要找的username";
            }
            
            
            
        }
        
        
    }
    public function get_ids_clear6($ids){
        if (count($ids)<=0){return [];}
        $ids_son = Db::name("fd_user")->where('pid','in',$ids)->column('id');
        $ids_son_son=[];
        if (count($ids_son)>0){
            $ids_son_son = self::get_ids_clear6($ids_son);
        }
        $ids_son = array_merge($ids_son,$ids_son_son);
        return $ids_son;
    }
    
    
    /**
     *  删除僵尸数据 用户等级为1，余额没有发生变化的，指定日期之前的数据
     * 
     */
    public function clear7(){
        if (Request::isPost()){
            $days = (int)Request::post("id");
            if (!empty($days)){
                $timestamp = strtotime($days);
                $ids = Db::name("fd_withdrawal")
                    ->where("status", "<>", 1)
                    ->where("create_time", "<", $timestamp)
                    ->column("id");
                if (!empty($ids)) {
                    $ret = Db::name("fd_withdrawal")
                        ->where("id", "in", $ids)
                        ->delete();
                    return $ret;
                }
            }
        }
    }
    /**
     *  删除僵尸数据 用户等级为1，余额没有发生变化的，指定日期之前的数据
     * 
     */
    public function clear8(){
        if (Request::isPost()){
            $days = (int)Request::post('id');
            
            if (!empty($days)){
                $timestamp = strtotime($days);
                
                // $timestamp = strtotime('-'.strval($days).' days');
                $ids = Db::name("fd_withdrawal")->where('status','1')->where('create_time','<',$timestamp)->column("id");
                $ret = Db::name("fd_withdrawal")->where('id','in',$ids)->delete();
                return $ret;    
            }
            
            
            
        }
        
        
    }
    
    
    /**
     * 修改系统能数配置
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function config()
    {
        $this->applyCsrfToken();
        if (Request::isGet()) {
            $this->fetch('system-config');
        }
        foreach (Request::post() as $key => $value) {
            sysconf($key, $value);
        }
        $this->success('系统参数配置成功！');
    }

    /**
     * 文件存储引擎
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function file()
    {
        $this->applyCsrfToken();
        if (Request::isGet()) {
            $this->type = input('type', 'local');
            $this->fetch("storage-{$this->type}");
        }
        $post = Request::post();
        if (isset($post['storage_type']) && isset($post['storage_local_exts'])) {
            $exts = array_unique(explode(',', strtolower($post['storage_local_exts'])));
            sort($exts);
            if (in_array('php', $exts)) $this->error('禁止上传可执行文件到本地服务器！');
            $post['storage_local_exts'] = join(',', $exts);
        }
        foreach ($post as $key => $value) sysconf($key, $value);
        if (isset($post['storage_type']) && $post['storage_type'] === 'oss') {
            try {
                $local = sysconf('storage_oss_domain');
                $bucket = $this->request->post('storage_oss_bucket');
                $domain = \library\File::instance('oss')->setBucket($bucket);
                if (empty($local) || stripos($local, '.aliyuncs.com') !== false) {
                    sysconf('storage_oss_domain', $domain);
                }
                $this->success('阿里云OSS存储配置成功！');
            } catch (HttpResponseException $exception) {
                throw $exception;
            } catch (\Exception $e) {
                $this->error("阿里云OSS存储配置失效，{$e->getMessage()}");
            }
        } else {
            $this->success('文件存储配置成功！');
        }
    }

}
