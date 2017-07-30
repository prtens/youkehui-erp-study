<?php

namespace Biz\Permission\Service\Impl;

use Biz\BaseService;
use Biz\Permission\Service\PermissionService;

class PermissionServiceImpl extends BaseService implements PermissionService
{
    public function findPermissionsByRoleCodes(array $roleCodes)
    {
        $roles = $this->getRoleService()->findRolesByCodes($roleCodes);

        $permissions = array();
        foreach ($roles as $role) {
            $permissions = array_merge($permissions, $role['access_rules']);
        }

        //todo过滤已经禁用的权限
        return $permissions;
    }

    /**
     * @return \Biz\Permission\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Permission:RoleService');
    }
}
