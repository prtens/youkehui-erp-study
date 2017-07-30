<?php

namespace Tests\Stock\Service;

use Biz\BaseTestCase;
use AppBundle\Common\PinyinToolkit;

class StockServiceTest extends BaseTestCase
{
    public function testCreateStock()
    {
        $createdStock = $this->createStock();
        $createdStock = current($createdStock);

        $goodsList = $this->mockStocks();
        $goods = $goodsList[0];

        $this->assertEquals($createdStock['goods_id'], $goods['id']);
    }

    public function testGetStock()
    {
        $createdStock = $this->createStock();
        $createdStock = current($createdStock);

        $gettedStock = $this->getStockService()->getStock($createdStock['id']);
        $this->assertArrayEquals($createdStock, $gettedStock);
    }

    public function testUpdateStock()
    {
        $createdStock = $this->createStock();
        $createdStock = current($createdStock);

        $fields = array(
            'seq' => 1,
            'min_amount' => 1,
        );

        $updatedStock = $this->getStockService()->updateStock($createdStock['id'], $fields);

        $this->assertArrayEquals(array_merge($createdStock, $fields), $updatedStock);
    }

    public function testUpdateStockByUpdateGoods()
    {
        $createdGoods = $this->createGoods();
        $createdStock = $this->createStock();
        $createdStock = current($createdStock);

        $fields = array(
            'provider_id' => 9998,
            'category_id' => 9999,
            'min_amount' => 1,
        );

        $updatedGoods = $this->getGoodsService()->updateGoods($createdStock['goods_id'], $fields);

        $updatedStock = $this->getStockService()->getStock($createdStock['id']);

        unset($fields['min_amount']);

        $this->assertArrayEquals($updatedGoods, $updatedStock, array_keys($fields));
    }

    public function testDeleteStock()
    {
        $createdStock = $this->createStock();
        $createdStock = current($createdStock);

        $this->getStockService()->deleteStock($createdStock['id']);

        $deleteStock = $this->getStockService()->getStock($createdStock['id']);

        $this->assertEquals(1, $deleteStock['is_deleted']);
    }

    public function testsearchStocks()
    {
        $this->createStock();

        $stocks = $this->getStockService()->searchStocks(
            array('category_id' => '1'),
            array('created_time' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        $this->assertEquals(2, count($stocks));
    }

    public function testCreateStockRecord()
    {
        $this->createGoods();
        $createdStock = $this->createStock();
        $createdStock = current($createdStock);

        $goodsId = $createdStock['goods_id'];

        $stockRecord = $this->getStockService()->createStockRecord(array(
            'goods_id' => $goodsId,
            'old_amount' => 0,
            'wave_amount' => 100,
            'cost_price' => 12,
            'type' => 'in',
        ));

        $goods = $this->getGoodsService()->getGoods($goodsId);

        $avgCostPrice = ($stockRecord['old_amount'] * $goods['cost_price'] + $stockRecord['wave_amount'] * $stockRecord['cost_price']) / $stockRecord['new_amount'];

        $this->assertEquals($avgCostPrice, $goods['cost_price']);
    }

    protected function createStock()
    {
        $stockList = $this->mockStocks();

        $createdStock = array();

        foreach ($stockList as $key => $stock) {
            $createdStock[$key] = $this->getStockService()->createStock($stock);
        }

        return $createdStock;
    }

    protected function mockStocks()
    {
        return array(
            array('id' => 1, 'goods_id' => 1, 'name' => '蛋糕', 'group_code' => 'product',
                'category_id' => 1, 'provider_id' => 2, 'min_amount' => 20, 'amount' => 0, ),
            array('id' => 2, 'goods_id' => 2, 'name' => '香蕉蛋糕', 'group_code' => 'product',
                'category_id' => 1, 'provider_id' => 2, 'min_amount' => 20, 'amount' => 0, ),
        );
    }

    protected function createGoods()
    {
        $goodsList = $this->mockGoods();

        $createdGoods = array();

        foreach ($goodsList as $key => $goods) {
            $goods['spell_code'] = PinyinToolkit::convert($goods['name']);
            $createdGoods[$key] = $this->getGoodsService()->createGoods($goods);
        }

        return $createdGoods;
    }

    protected function mockGoods()
    {
        return array(
            array('id' => 1, 'name' => '草莓蛋糕', 'small_picture' => '', 'cost_price' => '12', 'min_amount' => '0',
                'category_id' => '2', 'spec' => '500g一盒', 'unit' => '盒', 'ingredient' => '草莓、牛油、蛋糕',
                'sale_price' => '12', 'group_code' => 'product', 'provider_id' => '3', ),
            array('id' => 2, 'name' => '榴莲蛋糕', 'small_picture' => '', 'cost_price' => '12', 'min_amount' => '0',
                'category_id' => '2', 'spec' => '500g一盒', 'unit' => '盒', 'ingredient' => '榴莲、牛油、蛋糕',
                'sale_price' => '12', 'group_code' => 'product', 'provider_id' => '3', ),
        );
    }

    /**
     * @return \Biz\Goods\Service\GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return \Biz\Stock\Service\StockService
     */
    protected function getStockService()
    {
        return $this->createService('Stock:StockService');
    }
}
