<?php

namespace Biz\Stock\Event;

use Codeages\Biz\Framework\Context\BizAware;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StockEventSubscriber extends BizAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'goods.create' => 'onGoodsCreate',
            'goods.update' => 'onGoodsUpdate',
            'goods.delete' => 'onGoodsDelete',
            'goods.true_delete' => 'onCompletelyDelete',
            'goods.undelete' => 'onGoodsUndelete',
            'stock.wave' => 'onStockWave',
        );
    }

    public function onGoodsCreate(Event $event)
    {
        $goods = $event->getSubject();
        $ownerId = $event->getArgument('owner_id');
        $minAmount = $event->getArgument('min_amount');

        $fields = array(
            'goods_id' => $goods['id'],
            'name' => $goods['name'],
            'group_code' => $goods['group_code'],
            'category_id' => $goods['category_id'],
            'provider_id' => $goods['provider_id'],
            'min_amount' => $minAmount,
            'owner_id' => $ownerId,
            'amount' => 0,
        );

        $this->getStockService()->createStock($fields);
    }

    public function onGoodsUpdate(Event $event)
    {
        $goods = $event->getSubject();
        $ownerId = $event->getArgument('owner_id');
        $minAmount = $event->getArgument('min_amount');

        $stock = $this->getStockService()->getStockByGoodsIdAndOwnerId($goods['id'], $ownerId);
        if (!empty($stock)) {
            $fields = array(
                'name' => $goods['name'],
                'category_id' => $goods['category_id'],
                'provider_id' => $goods['provider_id'],
            );

            $this->getStockService()->updateStock($stock['id'], array('min_amount' => $minAmount));

            $this->getStockService()->updateStockByGoodsId($goods['id'], $fields);
        }
    }

    public function onGoodsDelete(Event $event)
    {
        $goods = $event->getSubject();

        $stock = $this->getStockService()->getStockByGoodsId($goods['id']);

        if (!empty($stock)) {
            $this->getStockService()->deleteStockByGoodsId($goods['id']);
        }
    }

    public function onCompletelyDelete(Event $event)
    {
        $goods = $event->getSubject();

        $stock = $this->getStockService()->getStockByGoodsId($goods['id']);

        if (!empty($stock)) {
            $this->getStockService()->trueDeleteStockByGoodsId($goods['id']);
        }
    }

    public function onGoodsUndelete(Event $event)
    {
        $goods = $event->getSubject();

        $stock = $this->getStockService()->getStockByGoodsId($goods['id']);

        if (!empty($stock)) {
            $this->getStockService()->undeleteStock($stock['id']);
        }
    }

    public function onStockWave(Event $event)
    {
        $stockRecord = $event->getSubject();
        $ownerId = $event->getArgument('owner_id');

        $stock = $this->getStockService()->getStockByGoodsIdAndOwnerId($stockRecord['goods_id'], $ownerId);

        if ($stock['amount'] < $stock['min_amount']) {
            $this->getStockService()->updateStock($stock['id'], array('is_warning' => 1));
        } else {
            $this->getStockService()->updateStock($stock['id'], array('is_warning' => 0));
        }
    }

    /**
     * @return \Biz\User\CurrentUser
     */
    public function getCurrentUser()
    {
        return $this->biz['user'];
    }

    /**
     * @return \Biz\Stock\Service\StockService
     */
    protected function getStockService()
    {
        return $this->biz->service('Stock:StockService');
    }

    /**
     * @return \Biz\Goods\Service\GoodsService
     */
    protected function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }
}
