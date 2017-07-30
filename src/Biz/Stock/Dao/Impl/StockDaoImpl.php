<?php

namespace Biz\Stock\Dao\Impl;

use Biz\Stock\Dao\StockDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class StockDaoImpl extends GeneralDaoImpl implements StockDao
{
    protected $table = 'stock';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'id',
                'seq',
                'created_time',
            ),
            'timestamps' => array(
                'created_time',
            ),
            'conditions' => array(
                'name like :name',
                'goods_id = :goods_id',
                'goods_id in (:goods_ids)',
                'provider_id = :provider_id',
                'category_id = :category_id',
                'owner_id = :owner_id',
                'group_code = :group_code',
                'is_warning = :is_warning',
                'is_deleted = :is_deleted',
            ),
        );
    }

    public function getByGoodsId($goodsId)
    {
        return $this->getByFields(array('goods_id' => $goodsId));
    }

    public function getByGoodsIdAndOwnerId($goodsId, $ownerId)
    {
        return $this->getByFields(array('goods_id' => $goodsId, 'owner_id' => $ownerId));
    }

    public function findByGoodsIds(array $goodsIds)
    {
        return $this->findInField('goods_id', $goodsIds);
    }

    public function updateByGoodsId($goodsId, $fields)
    {
        $this->db()->update($this->table, $fields, array('goods_id' => $goodsId));
    }

    public function deleteByGoodsId($goodsId)
    {
        $sql = "delete FROM {$this->table} WHERE goods_id = ?";

        return $this->db()->executeUpdate($sql, array($goodsId));
    }
}
