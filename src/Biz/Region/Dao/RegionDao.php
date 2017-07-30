<?php

namespace Biz\Region\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface RegionDao extends GeneralDaoInterface
{
    public function getByCode($code);

    public function findByParentId($parentId);

    public function findByIds(array $ids);

    public function findRegions();
}
