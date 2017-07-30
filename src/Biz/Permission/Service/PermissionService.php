<?php

namespace Biz\Permission\Service;

interface PermissionService
{
    public function findPermissionsByRoleCodes(array $roleCodes);
}
