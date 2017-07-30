<?php

namespace Biz\System\Dao\Impl;

use Biz\System\Dao\SettingDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SettingDaoImpl extends GeneralDaoImpl implements SettingDao
{
    protected $table = 'setting';

    public function declares()
    {
        return array(
            'serializes' => array(
                'value' => 'php',
            ),
        );
    }

    public function findByOwnerId($ownerId)
    {
        return $this->findByFields(array('owner_id' => $ownerId));
    }

    public function deleteByNameAndOwnerId($name, $ownerId)
    {
        return $this->db()->delete($this->table, array('name' => $name, 'owner_id' => $ownerId));
    }
}
