<?php

namespace Biz\System\Dao\Impl;

use Biz\System\Dao\LogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class LogDaoImpl extends GeneralDaoImpl implements LogDao
{
    protected $table = 'log';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'created_time',
                'id',
            ),
            'serializes' => array(
                'roles' => 'json',
            ),
            'conditions' => array(
                'module = :module',
                'action = :action',
                'level = :level',
                'user_id = :user_id',
                'created_time > :start_date_time',
                'created_time < :endDateTime',
                'created_time >= :start_date_time_GE',
                'user_id IN ( :user_ids )',
            ),
        );
    }

    public function analysisLoginNumByTime($startTime, $endTime)
    {
        $sql = "SELECT count(distinct userid)  as num FROM `{$this->table}` WHERE `action`='login_success' AND  `created_time`>= ? AND `created_time`<= ?  ";

        return $this->db()->fetchColumn($sql, array($startTime, $endTime));
    }

    public function analysisLoginDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count(distinct userid) as count, from_unixtime(created_time,'%Y-%m-%d') as date FROM `{$this->table}` WHERE `action`='login_success' AND `created_time`>= ? AND `created_time`<= ? group by from_unixtime(`created_time`,'%Y-%m-%d') order by date ASC ";

        return $this->db()->fetchAll($sql, array($startTime, $endTime));
    }
}
