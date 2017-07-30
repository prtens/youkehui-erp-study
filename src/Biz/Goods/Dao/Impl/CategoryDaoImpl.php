<?php

namespace Biz\Goods\Dao\Impl;

use Biz\Goods\Dao\CategoryDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CategoryDaoImpl extends GeneralDaoImpl implements CategoryDao
{
    protected $table = 'goods_category';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'parent_id',
                'depth',
            ),
        );
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByParentId($parentId)
    {
        return $this->findByFields(array('parent_id' => $parentId));
    }

    public function findByGroupCodeAndOwnerId($groupCode, $ownerId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE group_code = ? and owner_id = ?";

        return $this->db()->fetchAll($sql, array($groupCode, $ownerId));
    }
}
