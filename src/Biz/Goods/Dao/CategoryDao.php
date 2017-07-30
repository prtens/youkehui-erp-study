<?php

namespace Biz\Goods\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CategoryDao extends GeneralDaoInterface
{
    public function findByIds(array $ids);

    public function findByParentId($parentId);

    public function findByGroupCodeAndOwnerId($groupCode, $ownerId);
}
