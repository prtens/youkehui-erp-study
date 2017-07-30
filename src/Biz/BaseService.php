<?php

namespace Biz;

use Codeages\Biz\Framework\Event\Event;

class BaseService extends \Codeages\Biz\Framework\Service\BaseService
{
    /**
     * @return \Biz\User\CurrentUser
     */
    public function getCurrentUser()
    {
        return $this->biz['user'];
    }

    /**
     * @return \Biz\BaseService
     */
    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    /**
     * @param  $alias
     *
     * @return \Codeages\Biz\Framework\Dao\GeneralDaoInterface
     */
    protected function createDao($alias)
    {
        return $this->biz->dao($alias);
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }

    /**
     * @param string      $eventName
     * @param Event|mixed $subject
     *
     * @return \Codeages\Biz\Framework\Event\Event
     */
    protected function dispatchEvent($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }

    /**
     * @return \Monolog\Logger
     */
    protected function getLogger()
    {
        return $this->biz['logger'];
    }

    protected function beginTransaction()
    {
        $this->biz['db']->beginTransaction();
    }

    protected function commit()
    {
        $this->biz['db']->commit();
    }

    protected function rollback()
    {
        $this->biz['db']->rollback();
    }
}
