<?php

namespace AppBundle\Extension\DataTag;

class RoleChoicesDataTag extends BaseDataTag
{
    public function getData(array $arguments)
    {
        $choices = array();

        $currentUser = $this->getCurrentUser();
        $roles = $this->getRoleService()->findRolesByOwnerId($currentUser->getParentId());

        foreach ($roles as $role) {
            if (in_array($role['code'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) {
                continue;
            }
            $choices[$role['code']] = $role['name'];
        }

        return $choices;
    }

    /**
     * @return \Biz\Permission\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Permission:RoleService');
    }
}
