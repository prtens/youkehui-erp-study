<?php

namespace Biz\Goods\Service;

interface GoodsService
{
    public function getGoods($id);

    public function findGoodsByIds($ids);

    public function countGoods($conditions);

    public function searchGoods($conditions, $orderBy, $start, $limit);

    public function createGoods(array $category);

    public function updateGoods($id, array $fields);

    public function deleteGoods($id);

    public function isGoodsSpellCodeAvailable($spellCode);

    public function undeleteGoods($id);

    public function updateGoodsCostPrice($id, $costPrice);

    public function trueDeleteGoods($id);

    public function canDeleteGoods($id);
}
