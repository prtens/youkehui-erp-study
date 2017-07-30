<?php

namespace Biz\Stock\Dao\Impl;

use Biz\Stock\Dao\StockRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class StockRecordDaoImpl extends GeneralDaoImpl implements StockRecordDao
{
    protected $table = 'stock_record';

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
                'goods_id = :goods_id',
            ),
        );
    }
}
