<?php

namespace Biz\System\Service;

interface LogService
{
    /**
     * 记录一般日志.
     *
     * @param string $module  模块
     * @param string $action  操作
     * @param string $message 记录的详情
     */
    public function info($module, $action, $message, array $data = null);

    /**
     * 记录警告日志.
     *
     * @param string $module  模块
     * @param string $action  操作
     * @param string $message 记录的详情
     */
    public function warning($module, $action, $message, array $data = null);

    /**
     * 记录错误日志.
     *
     * @param string $module  模块
     * @param string $action  操作
     * @param string $message 记录的详情
     */
    public function error($module, $action, $message, array $data = null);

    public function searchLogs($conditions, $orderBy, $start, $limit);

    public function searchLogCount($conditions);

    public function analysisLoginNumByTime($startTime, $endTime);

    public function analysisLoginDataByTime($startTime, $endTime);
}
