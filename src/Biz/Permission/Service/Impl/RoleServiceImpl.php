<?php

namespace Biz\Permission\Service\Impl;

use Biz\BaseService;
use Biz\Permission\Service\RoleService;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\Exception\NotFoundException;
use Biz\Common\Exception\InvalidArgumentException;

class RoleServiceImpl extends BaseService implements RoleService
{
    public function getRole($id)
    {
        return $this->getRoleDao()->get($id);
    }

    public function getRoleByCode($code)
    {
        return $this->getRoleDao()->getByCode($code);
    }

    public function findRolesByCodes(array $codes)
    {
        return $this->getRoleDao()->findByCodes($codes);
    }

    public function findRolesByOwnerId($ownerId)
    {
        return $this->getRoleDao()->findByOwnerId($ownerId);
    }

    public function countRoles($conditions)
    {
        return $this->getRoleDao()->count($conditions);
    }

    public function searchRoles($conditions, $orderBy, $start, $limit)
    {
        return $this->getRoleDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function createRole(array $role)
    {
        $role = $this->filterCreateRoleFields($role);

        $role = $this->getRoleDao()->create($role);

        $this->getLogService()->info('region', 'create', '新增角色', $role);

        return $role;
    }

    public function updateRole($id, array $fields)
    {
        $role = $this->getRole($id);

        if (empty($role)) {
            throw new NotFoundException(sprintf('region id#%s not found', $id));
        }

        $fields = $this->filterUpdateRoleFields($fields);

        $role = $this->getRoleDao()->update($id, $fields);

        $this->getLogService()->info('region', 'update', '更新角色', $role);

        return $role;
    }

    public function deleteRole($id)
    {
        $role = $this->getRole($id);

        if (empty($role)) {
            throw new NotFoundException(sprintf('Role id#%s not found', $id));
        }

        $this->getRoleDao()->delete($id);

        $this->getLogService()->info('region', 'delete', '删除角色');
    }

    public function isRoleCodeAvailable($code)
    {
        if (strpos($code, 'ROLE_') !== 0) {
            return false;
        }

        $role = $this->getRoleByCode($code);

        return $role ? false : true;
    }

    public function isRoleNameAvailable($name)
    {
        $role = $this->getRoleDao()->getByNameAndOwner($name, $this->getCurrentUser()->getParentId());

        return $role ? false : true;
    }

    protected function filterCreateRoleFields($fields)
    {
        $requiredFields = array(
            'name',
            'code',
        );

        if (!ArrayToolkit::requires($fields, $requiredFields)) {
            throw new InvalidArgumentException('Missing required fields when creating Role');
        }

        $user = $this->getCurrentUser();

        $default = array(
            'access_rules' => '',
            'owner_id' => $user->getParentId(),
            'is_system' => 0,
        );

        $fields = ArrayToolkit::parts($fields, array_merge($requiredFields, array_keys($default)));

        $fields = array_merge($default, $fields);

        return $fields;
    }

    protected function filterUpdateRoleFields($fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'name',
            'access_rules',
        ));

        return $fields;
    }

    /**
     * @return \Biz\Permission\Dao\RoleDao
     */
    protected function getRoleDao()
    {
        return $this->createDao('Permission:RoleDao');
    }

    /**
     * @return \Biz\System\Service\LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
