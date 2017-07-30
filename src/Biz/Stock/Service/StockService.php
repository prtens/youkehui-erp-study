<?php

namespace Biz\Stock\Service;

interface StockService
{
    public function getStock($id);

    public function countStocks($conditions);

    public function searchStocks($conditions, $orderBy, $start, $limit);

    public function countStockRecords($conditions);

    public function searchStockRecords($conditions, $orderBy, $start, $limit);

    public function createStock(array $stock);

    public function updateStock($id, array $fields);

    public function updateStockByGoodsId($goodsId, array $fields);

    public function updateStockAmount($id, $amount);

    public function deleteStock($id);

    public function deleteStockByGoodsId($goodsId);

    public function trueDeleteStockByGoodsId($goodsId);

    public function undeleteStock($id);

    public function getStockByGoodsId($goodsId);

    public function getStockByGoodsIdAndOwnerId($goodsId, $ownerId);

    public function findStocksByGoodsIds(array $goodsIds);

    public function findStocksByGoodsIdsAndOwnerId(array $goodsIds, $ownerId);

    public function createStockRecord(array $stockRecord);

    public function batchCreateStockRecord(array $stockRecords);

    public function findToDosByTargetTypeAndCreatorId($targetType, $creatorId);

    public function findToDosByTargetTypeAndTargetId($targetType, $targetId);

    public function getToDo($id);

    public function createToDo(array $fields);

    public function deleteToDo($id);
}
