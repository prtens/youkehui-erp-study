<?php

namespace Biz\Stock\Service\Impl;

use Biz\BaseService;
use Biz\Stock\Service\StockService;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\Exception\NotFoundException;
use Biz\Common\Exception\InvalidArgumentException;
use Biz\Common\Exception\AccessDeniedException;
use Biz\Common\Exception\RuntimeException;

class StockServiceImpl extends BaseService implements StockService
{
    public function getStock($id)
    {
        return $this->getStockDao()->get($id);
    }

    public function countStocks($conditions)
    {
        return $this->getStockDao()->count($conditions);
    }

    public function searchStocks($conditions, $orderBy, $start, $limit)
    {
        return $this->getStockDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countStockRecords($conditions)
    {
        return $this->getStockRecordDao()->count($conditions);
    }

    public function searchStockRecords($conditions, $orderBy, $start, $limit)
    {
        return $this->getStockRecordDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function createStock(array $stock)
    {
        /*$checkStock = $this->getStockByGoodsIdAndOwnerId($stock['goods_id']);
        if(!empty($checkStock)){
            throw new RuntimeException(sprintf('the goods(id#%s) stock is excised', $stock['goods_id']));
        }*/
        $stock = $this->filterCreateStockFields($stock);

        return $this->getStockDao()->create($stock);
    }

    public function updateStock($id, array $fields)
    {
        $stock = $this->getStock($id);

        if (empty($stock)) {
            throw new NotFoundException(sprintf('stock id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($stock);

        $fields = $this->filterUpdateStockFields($fields);

        $stock = $this->getStockDao()->update($id, $fields);

        $this->getLogService()->info('stock', 'update', "更新库存(#{$stock['id']})", $stock);

        return $stock;
    }

    public function updateStockByGoodsId($goodsId, array $fields)
    {
        $fields = $this->filterUpdateStockFields($fields);

        $stock = $this->getStockDao()->updateByGoodsId($goodsId, $fields);

        return $stock;
    }

    public function updateStockAmount($id, $amount)
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('amount must be a numeric value');
        }

        $stock = $this->getStock($id);

        if (empty($stock)) {
            throw new NotFoundException(sprintf('stock id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($stock);

        $stock = $this->getStockDao()->update($id, array('amount' => $amount));

        return $stock['amount'];
    }

    public function deleteStock($id)
    {
        $stock = $this->getStock($id);

        if (empty($stock)) {
            throw new NotFoundException(sprintf('stock id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($stock);

        $this->getStockDao()->update($id, array('is_deleted' => 1));
    }

    public function deleteStockByGoodsId($goodsId)
    {
        $this->getStockDao()->updateByGoodsId($goodsId, array('is_deleted' => 1));
    }

    public function trueDeleteStockByGoodsId($goodsId)
    {
        $this->getStockDao()->deleteByGoodsId($goodsId);
    }

    public function undeleteStock($id)
    {
        $stock = $this->getStock($id);

        if (empty($stock)) {
            throw new NotFoundException(sprintf('stock id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($stock);

        $this->getStockDao()->update($id, array('is_deleted' => 0));
    }

    public function getStockByGoodsId($goodsId)
    {
        return $this->getStockDao()->getByGoodsId($goodsId);
    }

    public function getStockByGoodsIdAndOwnerId($goodsId, $ownerId)
    {
        return $this->getStockDao()->getByGoodsIdAndOwnerId($goodsId, $ownerId);
    }

    public function findStocksByGoodsIds(array $goodsIds)
    {
        return ArrayToolkit::index($this->getStockDao()->findByGoodsIds($goodsIds), 'goods_id');
    }

    public function findStocksByGoodsIdsAndOwnerId(array $goodsIds, $ownerId)
    {
        $stocks = $this->searchStocks(array(
            'goods_ids' => $goodsIds,
            'owner_id' => $ownerId,
        ), array('id' => 'ASC'), 0, PHP_INT_MAX);

        return ArrayToolkit::index($stocks, 'goods_id');
    }

    public function createStockRecord(array $stockRecord)
    {
        $stockRecord = $this->filterCreateStockRecordFields($stockRecord);

        $stockRecord = $this->getStockRecordDao()->create($stockRecord);

        $this->dispatchEvent('stock_record.create', $stockRecord);

        return $stockRecord;
    }

    public function batchCreateStockRecord(array $stockRecords)
    {
        $user = $this->getCurrentUser();
        $res = array();
        foreach ($stockRecords as $stockRecord) {
            $res[] = $this->createStockRecord($stockRecord);

            $this->dispatchEvent('stock.wave', $stockRecord, array('owner_id' => $user->getParentId()));
        }

        return $res;
    }

    public function findToDosByTargetTypeAndCreatorId($targetType, $creatorId)
    {
        return $this->getStockToDoDao()->findByTargetTypeAndCreatorId($targetType, $creatorId);
    }

    public function findToDosByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getStockToDoDao()->findByTargetTypeAndTargetId($targetType, $targetId);
    }

    public function getToDo($id)
    {
        return $this->getStockToDoDao()->get($id);
    }

    public function createToDo(array $todo)
    {
        $user = $this->getCurrentUser();

        $todo = $this->filterCreateStockTodoFields($todo);

        return $this->getStockToDoDao()->create($todo);
    }

    public function deleteToDo($id)
    {
        $toDo = $this->getStockToDoDao()->get($id);

        if (empty($toDo)) {
            throw new NotFoundException(sprintf('todo id#%s not found', $toDo));
        }

        $user = $this->getCurrentUser();
        if ($toDo['creator_id'] != $user->getId()) {
            throw new AccessDeniedException();
        }

        return $this->getStockToDoDao()->delete($id);
    }

    protected function filterCreateStockFields($fields)
    {
        $requiredFields = array(
            'goods_id',
            'name',
            'group_code',
            'category_id',
            'min_amount',
            'provider_id',
            'amount',
        );

        if (!ArrayToolkit::requires($fields, $requiredFields)) {
            throw new InvalidArgumentException('Missing required fields when creating stock');
        }

        $user = $this->getCurrentUser();

        $default = array(
            'seq' => 0,
            'owner_id' => $user->getParentId(),
        );

        $fields = ArrayToolkit::parts($fields, array_merge($requiredFields, array_keys($default)));
        $fields = array_merge($default, $fields);

        return $fields;
    }

    protected function filterUpdateStockFields($fields)
    {
        // 只保留允许更新的字段
        $fields = ArrayToolkit::parts($fields, array(
            'name',
            'category_id',
            'provider_id',
            'is_warning',
            'min_amount',
            'seq',
        ));

        return $fields;
    }

    protected function filterCreateStockRecordFields($fields)
    {
        $requiredFields = array(
            'goods_id',
            'type',
            'wave_amount',
            'cost_price',
        );

        if (!ArrayToolkit::requires($fields, $requiredFields)) {
            throw new InvalidArgumentException('Missing required fields when creating stock record');
        }

        $stock = $this->getStockByGoodsId($fields['goods_id']);
        $user = $this->getCurrentUser();

        $default = array(
            'operator_id' => $user->getId(),
            'owner_id' => $user->getParentId(),
            'new_amount' => $stock['amount'] + $fields['wave_amount'],
            'old_amount' => $stock['amount'],
            'remark' => '',
        );

        $fields = ArrayToolkit::parts($fields, array_merge($requiredFields, array_keys($default)));
        $fields = array_merge($default, $fields);

        return $fields;
    }

    protected function filterCreateStockTodoFields($fields)
    {
        $requiredFields = array(
            'target_type',
            'target_id',
        );

        if (!ArrayToolkit::requires($fields, $requiredFields)) {
            throw new InvalidArgumentException('Missing required fields when creating stock record');
        }

        $user = $this->getCurrentUser();
        $default = array(
            'creator_id' => $user->getId(),
        );

        $fields = ArrayToolkit::parts($fields, array_merge($requiredFields, array_keys($default)));
        $fields = array_merge($default, $fields);

        return $fields;
    }

    protected function checkCurrentUserAccess($stock)
    {
        $user = $this->getCurrentUser();

        if ($stock['owner_id'] != $user->getParentId()) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @return \Biz\Stock\Dao\StockDao
     */
    protected function getStockDao()
    {
        return $this->createDao('Stock:StockDao');
    }

    /**
     * @return \Biz\Stock\Dao\StockToDoDao
     */
    protected function getStockToDoDao()
    {
        return $this->createDao('Stock:StockToDoDao');
    }

    /**
     * @return \Biz\Stock\Dao\StockRecordDao
     */
    protected function getStockRecordDao()
    {
        return $this->createDao('Stock:StockRecordDao');
    }

    /**
     * @return \Biz\System\Service\LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
