<?php

namespace Biz\Permission\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface RoleDao extends GeneralDaoInterface
{
    public function getByCode($code);

    public function getByNameAndOwner($name, $ownerId);

    public function findByCodes(array $codes);

    public function findByOwnerId($ownerId);
}
