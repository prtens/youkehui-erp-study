<?php

namespace Biz\Permission\Service;

interface RoleService
{
    public function getRole($id);

    public function getRoleByCode($code);

    public function findRolesByCodes(array $codes);

    public function findRolesByOwnerId($ownerId);

    public function countRoles($conditions);

    public function searchRoles($conditions, $orderBy, $start, $limit);

    public function createRole(array $role);

    public function updateRole($id, array $fields);

    public function deleteRole($id);

    public function isRoleCodeAvailable($code);

    public function isRoleNameAvailable($name);
}
