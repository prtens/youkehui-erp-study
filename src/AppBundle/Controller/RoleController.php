<?php

namespace AppBundle\Controller;

use Biz\Common\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\PinyinToolkit;

class RoleController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $defaultConditions = array(
            'owner_id' => $this->getCurrentUser()->getParentId(),
        );

        $conditions = array_merge($defaultConditions, $conditions);

        $paginator = new Paginator(
            $request,
            $this->getRoleService()->countRoles($conditions),
            20
        );

        $roles = $this->getRoleService()->searchRoles(
            $conditions,
            array('id' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('AppBundle:role:index.html.twig', array(
            'roles' => $roles,
            'paginator' => $paginator,
        ));
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $fields['code'] = $this->generateCode($fields['name']);

            $role = $this->getRoleService()->createRole($fields);

            return $this->createJsonResponse(true);
        }

        $defaultRole = array(
            'id' => 0,
            'name' => '',
            'code' => '',
            'access_rules' => array(),
        );

        return $this->render('AppBundle:role:modal.html.twig', array(
            'role' => $defaultRole,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $role = $this->getRoleService()->getRole($id);

        if (empty($role)) {
            throw new NotFoundException(sprintf('role id#%s not found', $id));
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $fields['code'] = $this->generateCode($fields['name']);

            $role = $this->getRoleService()->updateRole($id, $fields);

            return $this->createJsonResponse(true);
        }

        return $this->render('AppBundle:role:modal.html.twig', array(
            'role' => $role,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $role = $this->getRoleService()->getRole($id);

        if (empty($role)) {
            throw new NotFoundException(sprintf('role id#%s not found', $id));
        }

        $user = $this->getCurrentUser();
        $count = $this->getUserService()->countUsers(
            array(
                'owner_id' => $user->getParentId(),
                'roles_like' => $role['code'],
            )
        );
        if ($count > 0) {
            return $this->createJsonResponse('无法删除，该角色正在使用中');
        }

        $this->getRoleService()->deleteRole($id);

        return $this->createJsonResponse(true);
    }

    public function validateNameAction(Request $request)
    {
        $exclude = $request->query->get('exclude');
        $name = $request->request->get('name');

        $available = $exclude == $name ? true : $this->getRoleService()->isRoleNameAvailable($name);

        if ($available) {
            return $this->createJsonCleanResponse(true);
        }

        return $this->createJsonCleanResponse('该名称已被占用，请重新输入');
    }

    protected function generateCode($name)
    {
        return 'ROLE_'.strtoupper(PinyinToolkit::convert($name));
    }

    /**
     * @return \Biz\Permission\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Permission:RoleService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
