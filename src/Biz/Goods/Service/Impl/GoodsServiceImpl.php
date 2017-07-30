<?php

namespace Biz\Goods\Service\Impl;

use Biz\BaseService;
use Biz\Goods\Service\GoodsService;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\Exception\NotFoundException;
use Biz\Common\Exception\InvalidArgumentException;
use Biz\Common\Exception\AccessDeniedException;

class GoodsServiceImpl extends BaseService implements GoodsService
{
    public function getGoods($id)
    {
        return $this->getGoodsDao()->get($id);
    }

    public function findGoodsByIds($ids)
    {
        return ArrayToolkit::index($this->getGoodsDao()->findByIds($ids), 'id');
    }

    public function countGoods($conditions)
    {
        return $this->getGoodsDao()->count($conditions);
    }

    public function searchGoods($conditions, $orderBy, $start, $limit)
    {
        return $this->getGoodsDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function createGoods(array $goods)
    {
        $minAmount = $goods['min_amount'];

        $goods = $this->filterCreateGoodsFields($goods);
        $goods = $this->getGoodsDao()->create($goods);

        $user = $this->getCurrentUser();
        $this->dispatchEvent('goods.create', $goods, array(
                'owner_id' => $user->getParentId(),
                'min_amount' => $minAmount,
            )
        );

        $goodsType = $goods['group_code'] == 'product' ? '产品' : '设备';
        $this->getLogService()->info('goods', 'create', "新增{$goodsType}-{$goods['name']}(#{$goods['id']})", $goods);

        return $goods;
    }

    public function updateGoods($id, array $fields)
    {
        $minAmount = $fields['min_amount'];
        $goods = $this->getGoods($id);

        if (empty($goods)) {
            throw new NotFoundException(sprintf('goods id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($goods);

        $fields = $this->filterUpdateGoodsFields($fields);

        $goods = $this->getGoodsDao()->update($id, $fields);

        $user = $this->getCurrentUser();
        $this->dispatchEvent('goods.update', $goods, array(
                'owner_id' => $user->getParentId(),
                'min_amount' => $minAmount,
            )
        );

        $goodsType = $goods['group_code'] == 'product' ? '产品' : '设备';
        $this->getLogService()->info('goods', 'update', "更新{$goodsType}-{$goods['name']}(#{$goods['id']})资料", $goods);

        return $goods;
    }

    public function isGoodsSpellCodeAvailable($spellCode)
    {
        $goods = $this->getGoodsDao()->getBySpellCode($spellCode);

        return $goods ? false : true;
    }

    public function deleteGoods($id)
    {
        $goods = $this->getGoods($id);

        if (empty($goods)) {
            throw new NotFoundException(sprintf('goods id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($goods);

        $this->getGoodsDao()->update($id, array('is_deleted' => 1));

        $this->dispatchEvent('goods.delete', $goods);

        $goodsType = $goods['group_code'] == 'product' ? '产品' : '设备';
        $this->getLogService()->info('goods', 'delete', "删除{$goodsType}-{$goods['name']}(#{$goods['id']})");
    }

    public function undeleteGoods($id)
    {
        $goods = $this->getGoods($id);

        if (empty($goods)) {
            throw new NotFoundException(sprintf('goods id#%s not found', $id));
        }

        $user = $this->getCurrentUser();
        if ($goods['owner_id'] != $user->getParentId()) {
            throw new AccessDeniedException();
        }

        $this->checkCurrentUserAccess($goods);

        $this->getGoodsDao()->update($id, array('is_deleted' => 0));

        $this->dispatchEvent('goods.undelete', $goods);

        $this->getLogService()->info('goods', 'undelete', "回收产品-{$goods['name']}(#{$goods['id']})");
    }

    public function updateGoodsCostPrice($id, $costPrice)
    {
        $goods = $this->getGoods($id);

        if (empty($goods)) {
            throw new NotFoundException(sprintf('goods id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($goods);

        $goods = $this->getGoodsDao()->update($id, array('cost_price' => $costPrice));

        return $goods['cost_price'];
    }

    public function trueDeleteGoods($id)
    {
        $goods = $this->getGoods($id);

        if (empty($goods)) {
            throw new NotFoundException(sprintf('goods id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($goods);

        $this->getGoodsDao()->delete($id);

        $this->dispatchEvent('goods.true_delete', $goods);

        $goodsType = $goods['group_code'] == 'product' ? '产品' : '设备';
        $this->getLogService()->info('goods', 'true_delete', "彻底删除{$goodsType}-{$goods['name']}(#{$goods['id']})");
    }

    public function canDeleteGoods($id)
    {
        $goods = $this->getGoods($id);

        if (empty($goods)) {
            throw new NotFoundException(sprintf('goods id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($goods);

        //不能删除的情况：关联了套餐
        $count = $this->getGoodsSetService()->countGoodsSetItems(array('goods_id' => $goods['id']));
        if ($count > 0) {
            return false;
        }

        return true;
    }

    protected function filterCreateGoodsFields($fields)
    {
        $requiredFields = array(
            'name',
            'spell_code',
            'category_id',
            'group_code',
            'spec',
            'unit',
        );

        if (!ArrayToolkit::requires($fields, $requiredFields)) {
            throw new InvalidArgumentException('Missing required fields when creating goods');
        }

        $user = $this->getCurrentUser();

        $default = array(
            'small_picture' => '',
            'medium_picture' => '',
            'large_picture' => '',
            'provider_id' => 0,
            'sale_price' => 0.00,
            'cost_price' => 0.00,
            'ingredient' => '',
            'about' => '',
            'seq' => 0,
            'owner_id' => $user->getParentId(),
            'owner_region_id' => $user['region_id'],
        );

        $fields = ArrayToolkit::parts($fields, array_merge($requiredFields, array_keys($default)));
        $fields = array_merge($default, $fields);

        return $fields;
    }

    protected function filterUpdateGoodsFields($fields)
    {
        // 只保留允许更新的字段
        $fields = ArrayToolkit::parts($fields, array(
            'name',
            'spell_code',
            'category_id',
            'provider_id',
            'sale_price',
            'small_picture',
            'medium_picture',
            'large_picture',
            'spec',
            'unit',
            'about',
            'seq',
        ));

        return $fields;
    }

    protected function checkCurrentUserAccess($goods)
    {
        $user = $this->getCurrentUser();

        if ($goods['owner_id'] != $user->getParentId()) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @return \Biz\Goods\Dao\GoodsDao
     */
    protected function getGoodsDao()
    {
        return $this->createDao('Goods:GoodsDao');
    }

    /**
     * @return \Biz\System\Service\LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return \Biz\Stock\Service\StockService
     */
    protected function getStockService()
    {
        return $this->createService('Stock:StockService');
    }

    /**
     * @return \Biz\GoodsSet\Service\GoodsSetService
     */
    protected function getGoodsSetService()
    {
        return $this->createService('GoodsSet:GoodsSetService');
    }
}
