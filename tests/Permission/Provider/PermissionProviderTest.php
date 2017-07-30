<?php

namespace Tests\Permission\Provider;

use Biz\BaseTestCase;

class PermissionProviderTest extends BaseTestCase
{
    public function testFindPermissionsByType()
    {
        $permissionProvider = $this->getPermissionProvider();
        $menus = $permissionProvider->findPermissionsByType('left_menu');
        $this->assertGreaterThan(0, count($menus));
    }

    /**
     * @return \Biz\Permission\Provider\PermissionProvider
     */
    protected function getPermissionProvider()
    {
        return self::$biz['permission_provider'];
    }
}
