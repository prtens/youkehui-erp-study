<?php

namespace tests\Log\Service;

use Biz\BaseTestCase;

class LogServiceTest extends BaseTestCase
{
    public function testCreateLog()
    {
        $createdLogs = $this->createLogs();
        $createdLog = $createdLogs[0];

        $logs = $this->mockLogs();
        $log = $logs[0];
        $this->assertArrayEquals($log, $createdLog, array_keys($log));
    }

    protected function createLogs()
    {
        $logs = $this->mockLogs();

        $createdLogs = array();

        foreach ($logs as $key => $log) {
            $createdLogs[$key] = $this->getLogService()->info($log['module'], $log['action'], $log['message']);
        }

        return $createdLogs;
    }

    protected function mockLogs()
    {
        $logs = array(
            array('module' => 'category', 'action' => 'create',
                'message' => '添加分类', 'data' => '', 'ip' => '127.0.0.1', ),
            array('module' => 'category', 'action' => 'create',
                'message' => '添加分类', 'data' => '', 'ip' => '127.0.0.1', ),
            array('module' => 'category', 'action' => 'create',
                'message' => '添加分类', 'data' => '', 'ip' => '127.0.0.1', ),
            array('module' => 'category', 'action' => 'create',
                'message' => '添加分类', 'data' => '', 'ip' => '127.0.0.1', ),
            array('module' => 'category', 'action' => 'create',
                'message' => '添加分类', 'data' => '', 'ip' => '127.0.0.1', ),
        );

        return $logs;
    }

    /**
     * @return \Biz\System\Service\LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
