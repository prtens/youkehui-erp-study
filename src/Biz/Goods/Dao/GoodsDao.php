<?php

namespace Biz\Goods\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface GoodsDao extends GeneralDaoInterface
{
    public function getBySpellCode($spellCode);

    public function findByIds(array $ids);
}
