<?php

namespace Biz\Stock\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface StockDao extends GeneralDaoInterface
{
    public function getByGoodsId($goodsId);

    public function getByGoodsIdAndOwnerId($goodsId, $userId);

    public function findByGoodsIds(array $goodsIds);

    public function updateByGoodsId($goodsId, $fields);

    public function deleteByGoodsId($goodsId);
}
