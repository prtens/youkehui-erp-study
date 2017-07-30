<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;

class SystemController extends BaseController
{
    public function logAction(Request $request)
    {
        $conditions = $request->query->all();

        $paginator = new Paginator(
            $request,
            $this->getLogService()->searchLogCount($conditions),
            20
        );

        $logs = $this->getLogService()->searchLogs(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($logs, 'user_id');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('AppBundle:admin/system:log.html.twig', array(
            'logs' => $logs,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return \Biz\System\Service\LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
