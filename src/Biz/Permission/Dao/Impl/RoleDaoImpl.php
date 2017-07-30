<?php

namespace Biz\Permission\Dao\Impl;

use Biz\Permission\Dao\RoleDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class RoleDaoImpl extends GeneralDaoImpl implements RoleDao
{
    protected $table = 'role';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'id',
            ),
            'serializes' => array(
                'access_rules' => 'json',
            ),
            'conditions' => array(
                'name = :name',
                'owner_id = :owner_id',
                'is_system = :is_system',
            ),
        );
    }

    public function getByCode($code)
    {
        return $this->getByFields(array('code' => $code));
    }

    public function getByNameAndOwner($name, $ownerId)
    {
        return $this->getByFields(array('name' => $name, 'owner_id' => $ownerId));
    }

    public function findByOwnerId($ownerId)
    {
        return $this->findByFields(array('owner_id' => $ownerId));
    }

    public function findByCodes(array $codes)
    {
        return $this->findInField('code', $codes);
    }
}
