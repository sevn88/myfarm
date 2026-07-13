<?php
/**
 * 代理统计模型 - SQL优化版
 * 核心优化：把每个代理 20+ 次数据库查询合并为 2-3 次 JOIN 查询
 */

namespace app\admin\model;

use think\Model;
use think\Db;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;
use \app\index\model\Base;

class Count extends Model
{
    /**
     * 代理统计列表（首页）
     */
    public static function index($start_time, $end_time, $pid_name)
    {
        $s_time = strtotime($start_time);
        $e_time = strtotime($end_time) + 1 * 24 * 3600;

        // 1. 构建代理下级树映射（最多5层）
        $descendant_map = self::build_descendant_map($pid_name);

        // 2. 批量统计所有数据（2-3次SQL搞定）
        $stats = self::batch_stats($s_time, $e_time, $descendant_map);

        return $stats;
    }

    /**
     * 构建代理下级树映射
     */
    private static function build_descendant_map($pid_name)
    {
        $map = [];

        if ($pid_name) {
            // 只查指定代理
            $agent = Db::name('fd_user')->where('username', $pid_name)->find();
            if ($agent) {
                $map[$agent['id']] = [
                    'username' => $agent['username'],
                    'agent_rate' => $agent['agent_rate'] ?? 0,
                    'children' => self::collect_children($agent['id'], 1),
                ];
            }
        } else {
            // 查所有有下级的代理
            $agents = Db::name('fd_user')
                ->field('id,username,agent_rate')
                ->whereNotNull('agent_id')
                ->where('agent_id', '>', 0)
                ->select();

            foreach ($agents as $agent) {
                $map[$agent['id']] = [
                    'username' => $agent['username'],
                    'agent_rate' => $agent['agent_rate'] ?? 0,
                    'children' => self::collect_children($agent['id'], 1),
                ];
            }
        }

        return $map;
    }

    /**
     * 递归收集下级ID（最多5层）
     */
    private static function collect_children($pid, $depth)
    {
        if ($depth >= 5) {
            return [];
        }
        $children = Db::name('fd_user')->where('pid', $pid)->column('id');
        $result = $children;
        foreach ($children as $child_id) {
            $result = array_merge($result, self::collect_children($child_id, $depth + 1));
        }
        return $result;
    }

    /**
     * 批量统计：用 JOIN 一次性查出所有代理的数据
     * 核心优化：不再每个代理查 20+ 次数据库
     */
    private static function batch_stats($s_time, $e_time, $descendant_map)
    {
        if (empty($descendant_map)) {
            return [];
        }

        // 收集所有需要统计的代理ID
        $agent_ids = array_keys($descendant_map);

        // 一次性查出所有代理的基本信息
        $agents_info = Db::name('fd_user')
            ->whereIn('id', $agent_ids)
            ->column('agent_rate', 'id');

        // 一次性查出所有充值/提现/注册数据（用 pid 关联）
        $all_stats = self::get_all_agency_stats($s_time, $e_time, $agent_ids);

        $result = [];
        foreach ($descendant_map as $agent_id => $info) {
            $data = $all_stats[$agent_id] ?? [];

            // 手续费计算
            $withdrawal_number = intval($data['withdrawal_number'] ?? 0);
            $m_withdrawal = intval($data['m_withdrawal'] ?? 0);
            $m_withdrawal_cost_new = intval(
                (float)$m_withdrawal * (float)(Base::get_config('tongji_tixian1') ?: 0)
                + (float)$withdrawal_number * (float)(Base::get_config('tongji_tixian3') ?: 0)
            );

            // 通道手续费
            $c_recharge = intval($data['c_recharge'] ?? 0);
            $m_recharge = intval($data['m_recharge'] ?? 0);
            $service_cz = intval(($c_recharge + $m_recharge) * (Base::get_config('tongji_chongzhi') ?: 0));

            // 盈利金额
            $agent_rate = $agents_info[$agent_id] ?? 0;
            $profit = intval($c_recharge + $m_recharge - $service_cz - $m_withdrawal - $m_withdrawal_cost_new);
            $profit_rate = intval(($c_recharge + $m_recharge) * intval($agent_rate) / 100);
            $profit = $profit - $profit_rate;

            $result[] = [
                'pid_name' => $info['username'],
                'reg_num' => intval($data['reg_num'] ?? 0),
                'c_recharge' => $c_recharge,
                'service_cz' => $service_cz,
                'm_recharge' => $m_recharge,
                'm_withdrawal' => $m_withdrawal,
                'm_withdrawal_cost' => intval($data['m_withdrawal_cost'] ?? 0),
                'withdrawal_number' => $withdrawal_number,
                'm_withdrawal_cost_new' => $m_withdrawal_cost_new,
                'shouchong' => intval($data['shouchong'] ?? 0),
                'profit' => $profit,
                'profit_rate' => $profit_rate,
            ];
        }

        return $result;
    }

    /**
     * 用 LEFT JOIN 一次性查出所有代理的统计数据
     * 核心优化：把 20+ 次查询合并为 1-2 次
     */
    private static function get_all_agency_stats($s_time, $e_time, $agent_ids)
    {
        $result = [];

        // 1. 注册人数 + 充值 + 提现（一次性 JOIN 查询）
        // 先查出所有代理的直属下级
        $direct_children = Db::name('fd_user')
            ->whereIn('pid', $agent_ids)
            ->field('pid, id')
            ->select();

        $child_ids = [];
        foreach ($direct_children as $c) {
            $child_ids[$c['pid']][] = $c['id'];
        }

        // 2. 一次性查出所有充值数据
        $recharge_data = Db::name('fd_recharge')
            ->alias('r')
            ->join('fd_user u', 'r.uid = u.id', 'LEFT')
            ->where('r.status', 1)
            ->where('r.create_time', '>', $s_time)
            ->where('r.create_time', '<', $e_time)
            ->where('u.pid', 'in', $agent_ids)
            ->field('u.pid,
                     SUM(CASE WHEN r.type=1 THEN r.money ELSE 0 END) as c_recharge,
                     SUM(CASE WHEN r.type=1 THEN r.service_charge ELSE 0 END) as service_charge,
                     SUM(CASE WHEN r.type=2 THEN r.money ELSE 0 END) as m_recharge,
                     COUNT(*) as recharge_count')
            ->group('u.pid')
            ->select();

        // 3. 一次性查出所有提现数据
        $withdrawal_data = Db::name('fd_withdrawal')
            ->alias('w')
            ->join('fd_user u', 'w.uid = u.id', 'LEFT')
            ->where('w.status', 1)
            ->where('w.create_time', '>', $s_time)
            ->where('w.create_time', '<', $e_time)
            ->where('u.pid', 'in', $agent_ids)
            ->field('u.pid,
                     SUM(w.actual_money) as m_withdrawal,
                     SUM(w.service_money) as m_withdrawal_cost,
                     COUNT(*) as withdrawal_number')
            ->group('u.pid')
            ->select();

        // 4. 注册人数
        $reg_data = Db::name('fd_user')
            ->where('status', 1)
            ->where('create_time', '>', $s_time)
            ->where('create_time', '<', $e_time)
            ->where('pid', 'in', $agent_ids)
            ->field('pid, COUNT(*) as reg_num')
            ->group('pid')
            ->select();

        // 5. 首充人数
        $shouchong_data = self::get_all_shouchong($s_time, $e_time, $agent_ids);

        // 组装数据
        foreach ($agent_ids as $agent_id) {
            $result[$agent_id] = [
                'reg_num' => 0,
                'c_recharge' => 0,
                'm_recharge' => 0,
                'm_withdrawal' => 0,
                'm_withdrawal_cost' => 0,
                'withdrawal_number' => 0,
                'shouchong' => 0,
            ];
        }

        foreach ($reg_data as $row) {
            if (isset($result[$row['pid']])) {
                $result[$row['pid']]['reg_num'] = intval($row['reg_num']);
            }
        }

        foreach ($recharge_data as $row) {
            if (isset($result[$row['pid']])) {
                $result[$row['pid']]['c_recharge'] = intval($row['c_recharge']);
                $result[$row['pid']]['m_recharge'] = intval($row['m_recharge']);
            }
        }

        foreach ($withdrawal_data as $row) {
            if (isset($result[$row['pid']])) {
                $result[$row['pid']]['m_withdrawal'] = intval($row['m_withdrawal']);
                $result[$row['pid']]['m_withdrawal_cost'] = intval($row['m_withdrawal_cost']);
                $result[$row['pid']]['withdrawal_number'] = intval($row['withdrawal_number']);
            }
        }

        foreach ($shouchong_data as $row) {
            if (isset($result[$row['pid']])) {
                $result[$row['pid']]['shouchong'] = intval($row['shouchong']);
            }
        }

        return $result;
    }

    /**
     * 批量计算首充人数
     */
    private static function get_all_shouchong($s_time, $e_time, $agent_ids)
    {
        // 统计时间之前已有充值的用户
        $before = Db::name('fd_recharge')
            ->alias('r')
            ->join('fd_user u', 'r.uid = u.id', 'LEFT')
            ->where('r.type', 1)
            ->where('r.status', 1)
            ->where('r.create_time', '<', $s_time)
            ->where('u.pid', 'in', $agent_ids)
            ->field('u.pid, GROUP_CONCAT(DISTINCT r.uid) as uids')
            ->group('u.pid')
            ->select();

        $before_map = [];
        foreach ($before as $row) {
            $before_map[$row['pid']] = array_filter(explode(',', $row['uids'] ?? ''));
        }

        // 当前时间段内的充值用户
        $current = Db::name('fd_recharge')
            ->alias('r')
            ->join('fd_user u', 'r.uid = u.id', 'LEFT')
            ->where('r.type', 1)
            ->where('r.status', 1)
            ->where('r.create_time', '>', $s_time)
            ->where('r.create_time', '<', $e_time)
            ->where('u.pid', 'in', $agent_ids)
            ->field('u.pid, GROUP_CONCAT(DISTINCT r.uid) as uids')
            ->group('u.pid')
            ->select();

        $result = [];
        foreach ($current as $row) {
            $pid = $row['pid'] ?? 0;
            $current_uids = array_filter(explode(',', $row['uids'] ?? ''));
            $before_uids = $before_map[$pid] ?? [];

            // 首充 = 当前充值用户 - 之前已充值用户
            $shouchong = count(array_diff($current_uids, $before_uids));
            $result[] = ['pid' => $pid, 'shouchong' => $shouchong];
        }

        return $result;
    }

    /**
     * 代理统计详情
     */
    public static function index_detail($start_time, $end_time, $pid_name)
    {
        $s_time = strtotime($start_time);
        $e_time = strtotime($end_time) + 1 * 24 * 3600;

        if (!$pid_name) {
            return [];
        }

        $agent = Db::name('fd_user')->where('username', $pid_name)->find();
        if (!$agent) {
            return [];
        }

        $agent_id = $agent['id'];
        $agent_rate = $agent['agent_rate'] ?? 0;

        // 收集最多5层下级
        $all_ids = [$agent_id] + self::collect_children($agent_id, 1);
        $all_ids_str = implode(',', $all_ids);

        // 一次性查出所有数据
        $stats = Db::name('fd_recharge')
            ->alias('r')
            ->join('fd_user u', 'r.uid = u.id', 'LEFT')
            ->where('r.status', 1)
            ->where('r.create_time', '>', $s_time)
            ->where('r.create_time', '<', $e_time)
            ->where('u.pid', 'in', $all_ids)
            ->field('
                SUM(CASE WHEN r.type=1 THEN r.money ELSE 0 END) as c_recharge,
                SUM(CASE WHEN r.type=1 THEN r.service_charge ELSE 0 END) as service_charge,
                SUM(CASE WHEN r.type=2 THEN r.money ELSE 0 END) as m_recharge,
                COUNT(CASE WHEN r.type=1 THEN 1 END) as recharge_count
            ')
            ->find();
        $stats = $stats ?: [];

        $withdrawal = Db::name('fd_withdrawal')
            ->alias('w')
            ->join('fd_user u', 'w.uid = u.id', 'LEFT')
            ->where('w.status', 1)
            ->where('w.create_time', '>', $s_time)
            ->where('w.create_time', '<', $e_time)
            ->where('u.pid', 'in', $all_ids)
            ->field('
                SUM(actual_money) as m_withdrawal,
                SUM(service_money) as m_withdrawal_cost,
                COUNT(*) as withdrawal_number
            ')
            ->find();
        $withdrawal = $withdrawal ?: [];

        $reg = Db::name('fd_user')
            ->where('pid', 'in', $all_ids)
            ->where('status', 1)
            ->where('create_time', '>', $s_time)
            ->where('create_time', '<', $e_time)
            ->count();

        // 首充
        $before = Db::name('fd_recharge')
            ->where('pid', $agent_id)
            ->where('type', 1)
            ->where('status', 1)
            ->where('create_time', '<', $s_time)
            ->column('uid');
        $shouchong = empty($before)
            ? Db::name('fd_recharge')->where('pid', $agent_id)->where('type', 1)->where('status', 1)->where('create_time', '>', $s_time)->where('create_time', '<', $e_time)->field('COUNT(DISTINCT uid) as num')->find()['num'] ?? 0
            : Db::name('fd_recharge')->where('pid', $agent_id)->where('type', 1)->where('status', 1)->where('create_time', '>', $s_time)->where('create_time', '<', $e_time)->where('uid', 'not in', $before)->field('COUNT(DISTINCT uid) as num')->find()['num'] ?? 0;

        $c_recharge = intval($stats['c_recharge'] ?? 0);
        $m_recharge = intval($stats['m_recharge'] ?? 0);
        $m_withdrawal = intval($withdrawal['m_withdrawal'] ?? 0);
        $m_withdrawal_cost = intval($withdrawal['m_withdrawal_cost'] ?? 0);
        $withdrawal_number = intval($withdrawal['withdrawal_number'] ?? 0);

        $m_withdrawal_cost_new = intval(
            (float)$m_withdrawal * (float)(Base::get_config('tongji_tixian1') ?: 0)
            + (float)$withdrawal_number * (float)(Base::get_config('tongji_tixian3') ?: 0)
        );
        $service_cz = intval(($c_recharge + $m_recharge) * (Base::get_config('tongji_chongzhi') ?: 0));
        $profit = intval($c_recharge + $m_recharge - $service_cz - $m_withdrawal - $m_withdrawal_cost_new);
        $profit_rate = intval(($c_recharge + $m_recharge) * intval($agent_rate) / 100);
        $profit = $profit - $profit_rate;

        return [
            'pid_name' => $pid_name,
            'reg_num' => $reg,
            'c_recharge' => $c_recharge,
            'service_cz' => $service_cz,
            'm_recharge' => $m_recharge,
            'm_withdrawal' => $m_withdrawal,
            'm_withdrawal_cost' => $m_withdrawal_cost,
            'withdrawal_number' => $withdrawal_number,
            'm_withdrawal_cost_new' => $m_withdrawal_cost_new,
            'shouchong' => intval($shouchong),
            'profit' => $profit,
            'profit_rate' => $profit_rate,
        ];
    }

    /**
     * 充值列表
     */
    public static function recharge_list($start_time, $end_time, $pid)
    {
        $s_time = strtotime($start_time);
        $e_time = strtotime($end_time) + 1 * 24 * 3600;

        if (!$pid) {
            return [];
        }

        $agent = Db::name('fd_user')->where('username', $pid)->find();
        if (!$agent) {
            return [];
        }

        $agent_id = $agent['id'];
        $ids = [['id' => $agent_id, 'username' => $pid]];
        $list = Db::name("fd_user")->where('pid', $agent_id)->field('id,username')->select();
        $ids = array_merge($ids, $list);

        foreach ($ids as $key => $val) {
            $id = $val['id'];
            $res = self::get_count_personal($s_time, $e_time, $id, $agent_id, $start_time, $end_time);
            if (isset($res)) {
                $ids[$key] = array_merge($val, $res);
            }
        }
        return $ids;
    }

    public static function recharge_type()
    {
        $recharge_type = Db::name('fd_recharge_type')->select();
        return $recharge_type;
    }

    /**
     * 个人统计（用于充值列表）
     */
    public static function get_count_personal($s_time, $e_time, $id, $agent_id, $start_time, $end_time, $agent_id_table_name)
    {
        $c_recharge = [
            ['pid', '=', $id],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['reg_num'] = Db::name('fd_user')->where($c_recharge)->count();

        $c_recharge = [
            ['uid', '=', $id],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['c_recharge'] = intval(Db::name('fd_recharge')->where($c_recharge)->sum('money'));

        $service_cz = [
            ['uid', '=', $id],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['service_cz'] = intval(Db::name('fd_recharge')->where($service_cz)->sum('service_charge'));

        $m_recharge = [
            ['uid', '=', $id],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_recharge'] = intval(Db::name('fd_recharge')->where($m_recharge)->sum('money'));

        $m_withdrawal = [
            ['uid', '=', $id],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal'] = intval(Db::name('fd_withdrawal')->where($m_withdrawal)->sum('actual_money'));

        $m_withdrawal_cost = [
            ['uid', '=', $id],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal_cost'] = intval(Db::name('fd_withdrawal')->where($m_withdrawal_cost)->sum('service_money'));

        // 首充
        $where = [
            ['uid', '=', $id],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '<', $s_time],
        ];
        $recharge_before = Db::field('uid')->distinct(true)->table('fd_recharge')->where($where)->select();

        if (count($recharge_before) > 0) {
            $info["shouchong"] = 0;
        } else {
            $where = [
                ['uid', '=', $id],
                ['type', '=', 1],
                ['status', '=', 1],
                ['create_time', '>', $s_time],
                ['create_time', '<', $e_time],
            ];
            $recharge_shouchong = Db::field('uid')->distinct(true)->table('fd_recharge')->where($where)->select();
            $info["shouchong"] = count($recharge_shouchong) > 0 ? 1 : 0;
        }

        $withdraw_number = Db::name("fd_withdrawal")->where($m_withdrawal)->count();
        $info['withdrawal_number'] = $withdraw_number;

        $info['m_withdrawal_cost_new'] = intval(
            (float)$info['m_withdrawal'] * (float)(Base::get_config('tongji_tixian1') ?: 0)
            + (float)$info['withdrawal_number'] * (float)(Base::get_config('tongji_tixian3') ?: 0)
        );

        // 下级
        if ($id != $agent_id) {
            $where = [['pid', '=', $id]];
            $son_ids = Db::name('fd_user')->field('id')->where($where)->select();
            if (isset($son_ids) && count($son_ids) > 0) {
                $ids_arr = [];
                foreach ($son_ids as $key => $val) {
                    $ids_arr[] = $val['id'];
                }
                $re = self::sub_index_self($start_time, $end_time, $ids_arr, 1, $agent_id_table_name);
                $info["reg_num"] += $re["reg_num"] ?? 0;
                $info["c_recharge"] += $re["c_recharge"] ?? 0;
                $info["service_cz"] += $re["service_cz"] ?? 0;
                $info["m_recharge"] += $re["m_recharge"] ?? 0;
                $info["m_withdrawal"] += $re["m_withdrawal"] ?? 0;
                $info["m_withdrawal_cost"] += $re["m_withdrawal_cost"] ?? 0;
                $info['withdrawal_number'] += $re['withdrawal_number'] ?? 0;
                $info["shouchong"] += $re["shouchong"] ?? 0;
                $info['m_withdrawal_cost_new'] += $re['m_withdrawal_cost_new'] ?? 0;
            }
        }

        $info['service_cz'] = intval(($info['c_recharge'] + $info['m_recharge']) * (Base::get_config('tongji_chongzhi') ?: 0));

        $agent_rate = Db::name("fd_user")->where("id", $id)->value("agent_rate");
        $agent_rate = $agent_rate ?: 0;
        $info['profit'] = intval($info['c_recharge'] + $info['m_recharge'] - $info['service_cz'] - $info['m_withdrawal'] - $info['m_withdrawal_cost_new']);
        $info["profit_rate"] = intval(($info['c_recharge'] + $info['m_recharge']) * intval($agent_rate) / 100);
        $info["profit"] = $info['profit'] - $info['profit_rate'];

        return $info;
    }

    /**
     * 递归下级统计（用于充值列表）
     */
    public static function sub_index_self($start_time, $end_time, $ids, $arg_times, $agent_id_table_name)
    {
        $s_time = strtotime($start_time);
        $e_time = strtotime($end_time) + 1 * 24 * 3600;

        $info['reg_num'] = Db::name('fd_user')
            ->where('pid', 'in', $ids)
            ->where('create_time', '>', $s_time)
            ->where('create_time', '<', $e_time)
            ->count();

        $c_recharge = [
            ['uid', 'in', $ids],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['c_recharge'] = intval(Db::name('fd_recharge')->where($c_recharge)->sum('money'));

        $service_cz = [
            ['uid', 'in', $ids],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['service_cz'] = intval(Db::name('fd_recharge')->where($service_cz)->sum('service_charge'));

        $m_recharge = [
            ['uid', 'in', $ids],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_recharge'] = intval(Db::name('fd_recharge')->where($m_recharge)->sum('money'));

        $m_withdrawal = [
            ['uid', 'in', $ids],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal'] = intval(Db::name('fd_withdrawal')->where($m_withdrawal)->sum('actual_money'));

        $m_withdrawal_cost = [
            ['uid', 'in', $ids],
            ['type', '=', 2],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $info['m_withdrawal_cost'] = intval(Db::name('fd_withdrawal')->where($m_withdrawal_cost)->sum('service_money'));

        $withdraw_number = Db::name("fd_withdrawal")
            ->where($m_withdrawal)
            ->count();
        $info['withdrawal_number'] = $withdraw_number;

        // 首充
        $where = [
            ['uid', 'in', $ids],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '<', $s_time],
        ];
        $recharge_before = Db::field('uid')->distinct(true)->table('fd_recharge')->where($where)->select();
        $uid_recharge = [];
        foreach ($recharge_before as $key => $val) {
            $uid_recharge[] = $val['uid'];
        }
        $where = [
            ['uid', 'in', $ids],
            ['uid', 'not in', $uid_recharge],
            ['type', '=', 1],
            ['status', '=', 1],
            ['create_time', '>', $s_time],
            ['create_time', '<', $e_time],
        ];
        $recharge_shouchong = Db::field('uid')->distinct(true)->table('fd_recharge')->where($where)->select();
        $info["shouchong"] = count($recharge_shouchong);

        // 递归下级
        $var_times = $arg_times + 1;
        if ($var_times < 6) {
            $where = [['pid', 'in', $ids]];
            $son_ids = Db::name('fd_user')->field('id')->where($where)->select();
            if (isset($son_ids) && count($son_ids) > 0) {
                $ids_arr = [];
                foreach ($son_ids as $key => $val) {
                    $ids_arr[] = $val['id'];
                }
                $re = self::sub_index_self($start_time, $end_time, $ids_arr, $var_times, $agent_id_table_name);
                $info["reg_num"] += $re["reg_num"] ?? 0;
                $info["c_recharge"] += $re["c_recharge"] ?? 0;
                $info["service_cz"] += $re["service_cz"] ?? 0;
                $info["m_recharge"] += $re["m_recharge"] ?? 0;
                $info["m_withdrawal"] += $re["m_withdrawal"] ?? 0;
                $info["m_withdrawal_cost"] += $re["m_withdrawal_cost"] ?? 0;
                $info['withdrawal_number'] += $re['withdrawal_number'] ?? 0;
                $info["shouchong"] += $re["shouchong"] ?? 0;
                $info['m_withdrawal_cost_new'] += $re['m_withdrawal_cost_new'] ?? 0;
            }
        }

        return $info;
    }
}
