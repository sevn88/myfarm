<?php

namespace app\admin\controller;

use library\Controller;

/**
 * Admin 基础控制器
 * 提供通用的结果处理方法，避免每个控制器重复写 switch-case
 */
class Base extends Controller
{
    /**
     * 处理 Model 返回的结果字符串，转换为统一响应
     *
     * Model 通常返回 'success' / 'error' / 'error_xxx' 等字符串
     * 此方法将其转换为标准 JSON 响应
     *
     * @param string $result Model 返回的结果
     * @param string $successMsg 成功时的消息
     * @param string $errorMsg 失败时的消息
     * @param array $extra 额外的 case => msg 映射
     * @return void
     */
    protected function handleResult(string $result, string $successMsg = '操作成功', string $errorMsg = '操作失败', array $extra = [])
    {
        if ($result === 'success') {
            return $this->success($successMsg);
        }
        if (isset($extra[$result])) {
            return $this->error($extra[$result]);
        }
        return $this->error($errorMsg);
    }

    /**
     * 处理 Model 返回的结果，返回 JSON（用于 AJAX 接口）
     *
     * @param string $result Model 返回的结果
     * @param string $successMsg 成功时的消息
     * @param string $errorMsg 失败时的消息
     * @param array $extra 额外的 case => msg 映射
     * @return array
     */
    protected function jsonResult(string $result, string $successMsg = '操作成功', string $errorMsg = '操作失败', array $extra = [])
    {
        if ($result === 'success') {
            return ['code' => 1, 'msg' => $successMsg];
        }
        if (isset($extra[$result])) {
            return ['code' => 0, 'msg' => $extra[$result]];
        }
        return ['code' => 0, 'msg' => $errorMsg];
    }
}
