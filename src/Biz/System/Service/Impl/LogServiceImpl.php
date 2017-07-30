<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\LogService;

class LogServiceImpl extends BaseService implements LogService
{
    public function info($module, $action, $message, array $data = null)
    {
        return $this->addLog('info', $module, $action, $message, $data);
    }

    public function warning($module, $action, $message, array $data = null)
    {
        return $this->addLog('warning', $module, $action, $message, $data);
    }

    public function error($module, $action, $message, array $data = null)
    {
        return $this->addLog('error', $module, $action, $message, $data);
    }

    public function searchLogs($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getLogDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchLogCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getLogDao()->count($conditions);
    }

    protected function addLog($level, $module, $action, $message, array $data = null)
    {
        $currentUser = $this->getCurrentUser();

        return $this->getLogDao()->create(
            array(
                'module' => $module,
                'action' => $action,
                'message' => $message,
                'data' => $data != null ? json_encode($data, JSON_UNESCAPED_UNICODE) : '',
                'user_id' => $currentUser['id'],
                'ip' => $currentUser['login_ip'],
                'created_time' => time(),
                'level' => $level,
            )
        );
    }

    public function analysisLoginNumByTime($startTime, $endTime)
    {
        return $this->getLogDao()->analysisLoginNumByTime($startTime, $endTime);
    }

    public function analysisLoginDataByTime($startTime, $endTime)
    {
        return $this->getLogDao()->analysisLoginDataByTime($startTime, $endTime);
    }

    protected function prepareSearchConditions($conditions)
    {
        if (!empty($conditions['startDateTime'])) {
            $conditions['startDateTime'] = strtotime($conditions['startDateTime']);
        }

        if (!empty($conditions['endDateTime'])) {
            $conditions['endDateTime'] = strtotime($conditions['endDateTime']);
        }

        return $conditions;
    }

    /**
     * @return \Biz\System\Dao\LogDao
     */
    protected function getLogDao()
    {
        return $this->createDao('System:LogDao');
    }
}
