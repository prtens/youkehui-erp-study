<?php

namespace Biz\Goods\Dao\Impl;

use Biz\Goods\Dao\GoodsDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class GoodsDaoImpl extends GeneralDaoImpl implements GoodsDao
{
    protected $table = 'goods';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'id',
                'created_time',
            ),
            'timestamps' => array(
                'created_time',
                'updated_time',
            ),
            'conditions' => array(
                'name like :name',
                'category_id = :category_id',
                'provider_id = :provider_id',
                'owner_id = :owner_id',
                'group_code = :group_code',
                'is_deleted = :is_deleted',
                'category_id in (:category_ids)',
            ),
        );
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getBySpellCode($spellCode)
    {
        return $this->getByFields(array('spell_code' => $spellCode));
    }
}
