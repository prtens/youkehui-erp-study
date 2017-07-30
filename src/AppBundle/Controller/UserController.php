<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Common\Exception\NotFoundException;
use Biz\Common\Exception\RuntimeException;

class UserController extends BaseController
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

        $currentUser = $this->getCurrentUser();

        $defaultConditions = array(
            'type' => $type,
        );

        if ($type == 'alias-subuser') {
            $defaultConditions['parent_id'] = $currentUser->getParentId();
        }

        $conditions = array_merge($defaultConditions, $conditions);

        $paginator = new Paginator(
            $request,
            $this->getUserService()->countUsers($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $regionIds = ArrayToolkit::column($users, 'region_id');
        $regions = $this->getRegionService()->findRegionsByIds($regionIds);

        $roles = $this->getRoleService()->findRolesByOwnerId($currentUser->getParentId());
        $roles = ArrayToolkit::index($roles, 'code');

        return $this->render("AppBundle:user:{$type}.html.twig", array(
            'users' => $users,
            'regions' => $regions,
            'roles' => $roles,
            'paginator' => $paginator,
        ));
    }

    public function validateNicknameAction(Request $request)
    {
        $exclude = $request->query->get('exclude');
        $nickname = $request->request->get('nickname');

        $available = $exclude == $nickname ? true : $this->getUserService()->isNicknameAvailable($nickname);

        if ($available) {
            return $this->createJsonCleanResponse(true);
        }

        return $this->createJsonCleanResponse('该名称已被占用，请重新输入');
    }

    public function subuserValidateNicknameAction(Request $request)
    {
        $exclude = $request->query->get('exclude');
        $nickname = $request->request->get('nickname');

        $available = $exclude == $nickname ? true : $this->getUserService()->isSubuserNicknameAvailable($nickname);

        if ($available) {
            return $this->createJsonCleanResponse(true);
        }

        return $this->createJsonCleanResponse('该名称已被占用，请重新输入');
    }

    public function validateMobileAction(Request $request)
    {
        $exclude = $request->query->get('exclude');
        $mobile = $request->request->get('mobile');

        $available = $exclude == $mobile ? true : $this->getUserService()->isMobileAvailable($mobile);

        if ($available) {
            return $this->createJsonCleanResponse(true);
        }

        return $this->createJsonCleanResponse('手机号已被占用，请重新输入');
    }

    public function validateEmailAction(Request $request)
    {
        $exclude = $request->query->get('exclude');
        $email = $request->request->get('email');

        $available = $exclude == $email ? true : $this->getUserService()->isEmailAvailable($email);

        if ($available) {
            return $this->createJsonCleanResponse(true);
        }

        return $this->createJsonCleanResponse('Email已被占用，请重新输入');
    }

    public function createAction(Request $request)
    {
        $type = $request->query->get('type');

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $fields['type'] = $type;

            $currentUser = $this->getCurrentUser();

            if ($type == 'alias') {
                $fields['parent_id'] = 0;
            } else {
                $fields['parent_id'] = $currentUser->getParentId();
                $fields['region_id'] = $currentUser['region_id'];
            }

            $this->getUserService()->register($fields);

            return $this->createJsonResponse(true);
        }

        $defaultUser = array(
            'id' => 0,
            'nickname' => '',
            'mobile' => '',
            'email' => '',
            'company_name' => '',
            'region_id' => 0,
            'company_address' => '',
            'parent_id' => 0,
            'type' => $type,
            'roles' => '',
        );

        return $this->render("AppBundle:user:modal-{$type}.html.twig", array(
            'user' => $defaultUser,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            throw new NotFoundException(sprintf('user id#%s not found', $id));
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $this->getUserService()->updateUser($id, $fields);

            return $this->createJsonResponse(true);
        }

        return $this->render("AppBundle:user:modal-{$user['type']}.html.twig", array(
            'user' => $user,
        ));
    }

    public function deleteAction($id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            throw new NotFoundException(sprintf('user id#%s not found', $id));
        }

        if (!$this->getUserService()->canDeleteUser($id)) {
            throw new RuntimeException(sprintf('无法删除"%s"，请先删除其名下的产品后重试', $user['nickname']));
        }

        $this->getUserService()->deleteUser($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids');

        foreach ($ids as $id) {
            $user = $this->getUserService()->getUser($id);

            if (empty($user)) {
                throw new NotFoundException(sprintf('User id#%s not found', $id));
            }
            $this->getUserService()->deleteUser($id);
        }

        return $this->createJsonResponse(true);
    }

    public function enableAction($id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            throw new NotFoundException(sprintf('user id#%s not found', $id));
        }

        $this->getUserService()->enableUser($id);

        return $this->createJsonResponse(true);
    }

    public function disableAction($id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            throw new NotFoundException(sprintf('user id#%s not found', $id));
        }

        $this->getUserService()->disableUser($id);

        return $this->createJsonResponse(true);
    }

    public function batchDisableAction(Request $request)
    {
        $ids = $request->request->get('ids');

        foreach ($ids as $id) {
            $user = $this->getUserService()->getUser($id);

            if (empty($user)) {
                throw new NotFoundException(sprintf('User id#%s not found', $id));
            }
            $this->getUserService()->disableUser($id);
        }

        return $this->createJsonResponse(true);
    }

    public function ajaxSearchAction(Request $request)
    {
        $keyword = $request->query->get('keyword');
        $type = $request->query->get('type');
        $rolesLike = $request->query->get('roles_like');
        $id = $request->query->get('id');

        $user = $this->getCurrentUser();

        $conditions = array(
            'nickname' => $keyword,
            'type' => $type,
            'region_id' => $user['region_id'],
            'roles_like' => $rolesLike,
            'id' => $id,
        );

        if ($rolesLike == 'ROLE_SALESMAN') {
            $conditions['parent_id'] = $user->getParentId();
        }

        // 默认返回20条结果
        $users = $this->getUserService()->searchUsers($conditions, array('updated_time' => 'desc'), 0, 20);
        $users = $this->filterUsersFields($users);

        return $this->createJsonCleanResponse($users);
    }

    public function changePasswordAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            throw new NotFoundException(sprintf('user id#%s not found', $id));
        }

        if ($request->getMethod() == 'POST') {
            $password = $request->request->get('password');
            $this->getUserService()->changePassword($id, $password);

            return $this->createJsonResponse(true);
        }

        return $this->render('AppBundle:user:change-password.html.twig', array(
            'user' => $user,
        ));
    }

    protected function filterUsersFields($users)
    {
        foreach ($users as &$user) {
            $user = $this->filterUserFields($user);
        }

        return $users;
    }

    protected function filterUserFields($user)
    {
        $publicFields = array(
            'id',
            'nickname',
            'company_name',
            'company_address',
            'small_avatar',
            'medium_avatar',
            'large_avatar',
        );

        return ArrayToolkit::parts($user, $publicFields);
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return \Biz\Region\Service\RegionService
     */
    protected function getRegionService()
    {
        return $this->createService('Region:RegionService');
    }

    /**
     * @return \Biz\Permission\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Permission:RoleService');
    }
}
