<?php

namespace Biz\Goods\Event;

use Codeages\Biz\Framework\Context\BizAware;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GoodsEventSubscriber extends BizAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'stock_record.create' => 'onStockRecordCreate',
        );
    }

    public function onStockRecordCreate(Event $event)
    {
        $stockRecord = $event->getSubject();

        $goodsId = $stockRecord['goods_id'];

        $stock = $this->getStockService()->getStockByGoodsId($goodsId);

        $goods = $this->getGoodsService()->getGoods($goodsId);

        if ($stockRecord['type'] == 'in') {
            $avgCostPrice = ($stockRecord['old_amount'] * $goods['cost_price'] + $stockRecord['wave_amount'] * $stockRecord['cost_price']) / $stockRecord['new_amount'];

            $this->getGoodsService()->updateGoodsCostPrice($goodsId, $avgCostPrice);
        }

        $this->getStockService()->updateStockAmount($stock['id'], $stockRecord['new_amount']);
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
