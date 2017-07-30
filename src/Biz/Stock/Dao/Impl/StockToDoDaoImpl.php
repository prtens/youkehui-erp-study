<?php

namespace Biz\Stock\Dao\Impl;

use Biz\Stock\Dao\StockToDoDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class StockToDoDaoImpl extends GeneralDaoImpl implements StockToDoDao
{
    protected $table = 'stock_todo';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'created_time',
                'id',
            ),
            'timestamps' => array(
                'created_time',
            ),
            'conditions' => array(
                'target_type = :target_type',
                'target_id = :target_id',
            ),
        );
    }

    public function findByTargetTypeAndCreatorId($targetType, $creatorId)
    {
        return $this->findByFields(array('target_type' => $targetType, 'creator_id' => $creatorId));
    }

    public function findByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->findByFields(array('target_type' => $targetType, 'target_id' => $targetId));
    }
}
