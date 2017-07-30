<?php

namespace Biz\Region\Dao\Impl;

use Biz\Region\Dao\RegionDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class RegionDaoImpl extends GeneralDaoImpl implements RegionDao
{
    protected $table = 'region';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'parent_id',
                'id',
            ),
            'conditions' => array(
                'depth = :depth ',
            ),
        );
    }

    public function getByCode($code)
    {
        return $this->getByFields(array('code' => $code));
    }

    public function findByParentId($parentId)
    {
        return $this->findByFields(array('parent_id' => $parentId));
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findRegions()
    {
        $sql = "SELECT * FROM {$this->table}";

        return $this->db()->fetchAll($sql);
    }
}
