<?php

namespace tests\Permission\Service;

use Biz\BaseTestCase;

class RoleServiceTest extends BaseTestCase
{
    public function testCreateRole()
    {
        $createdRoles = $this->createRoles();
        $createdRole = $createdRoles[0];

        $roles = $this->mockRoles();
        $region = $roles[0];

        $this->assertArrayEquals($region, $createdRole, array_keys($region));
    }

    public function testGetRole()
    {
        $createdRoles = $this->createRoles();
        $createdRole = $createdRoles[0];

        $gettedRole = $this->getRoleService()->getRole($createdRole['id']);
        $this->assertArrayEquals($createdRole, $gettedRole);
    }

    public function testUpdateRole()
    {
        $createdRoles = $this->createRoles();
        $createdRole = $createdRoles[0];

        $fields = array(
            'name' => '销售员',
            'access_rules' => array('url1', 'url2'),
        );

        $updateRole = $this->getRoleService()->updateRole($createdRole['id'], $fields);

        $this->assertArrayEquals(array_merge($createdRole, $fields), $updateRole);
    }

    public function testDeleteRole()
    {
        $createdRoles = $this->createRoles();
        $createdRole = $createdRoles[0];

        $this->getRoleService()->deleteRole($createdRole['id']);

        $deleteRole = $this->getRoleService()->getRole($createdRole['id']);

        $this->assertEquals(null, $deleteRole);
    }

    public function testIsRoleCodeAvailable()
    {
        $createdRoles = $this->createRoles();
        $createdRole = $createdRoles[0];

        $available = $this->getRoleService()->isRoleCodeAvailable($createdRole['code']);
        $this->assertEquals(false, $available);

        $available = $this->getRoleService()->isRoleCodeAvailable('XXXX');
        $this->assertEquals(false, $available);

        $available = $this->getRoleService()->isRoleCodeAvailable('ROLE_XXXX');
        $this->assertEquals(true, $available);
    }

    protected function createRoles()
    {
        $roles = $this->mockRoles();

        $createdRoles = array();

        foreach ($roles as $key => $region) {
            $createdRoles[$key] = $this->getRoleService()->createRole($region);
        }

        return $createdRoles;
    }

    protected function mockRoles()
    {
        $roles = array(
            array('owner_id' => '1', 'name' => '销售', 'code' => 'ROLE_SALESMAN'),
            array('owner_id' => '1', 'name' => '经理', 'code' => 'ROLE_MANAGER'),
        );

        return $roles;
    }

    /**
     * @return \Biz\Permission\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Permission:RoleService');
    }
}
