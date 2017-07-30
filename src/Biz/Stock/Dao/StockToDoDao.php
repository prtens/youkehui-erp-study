<?php

namespace Biz\Stock\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface StockToDoDao extends GeneralDaoInterface
{
    public function findByTargetTypeAndCreatorId($targetType, $creatorId);

    public function findByTargetTypeAndTargetId($targetType, $targetId);
}
