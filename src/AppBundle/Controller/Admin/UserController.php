<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\UserController as BaseUserController;

class UserController extends BaseUserController
{
    public function indexAction(Request $request, $type)
    {
        $conditions = $request->query->all();

        if (!empty($conditions['region_id'])) {
            $children = $this->getRegionService()->findRegionsByParentId($conditions['region_id']);
            if (!empty($children)) {
                $childrenIds = ArrayToolkit::column($children, 'id');

                array_push($childrenIds, $conditions['region_id']);
                $conditions['region_ids'] = $childrenIds;
                unset($conditions['region_id']);
            }
        }

        $defaultConditions = array(
            'type' => $type,
        );

        $conditions = array_merge($defaultConditions, $conditions);

        $paginator = new Paginator(
            $request,
            $this->getUserService()->countUsers($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $regionIds = ArrayToolkit::column($users, 'region_id');
        $regions = $this->getRegionService()->findRegionsByIds($regionIds);

        return $this->render("AppBundle:admin/user:{$type}.html.twig", array(
            'users' => $users,
            'regions' => $regions,
            'paginator' => $paginator,
        ));
    }
}
