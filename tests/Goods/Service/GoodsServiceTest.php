<?php

namespace Tests\Goods\Service;

use Biz\BaseTestCase;
use AppBundle\Common\PinyinToolkit;

class GoodsServiceTest extends BaseTestCase
{
    public function testCreateGoods()
    {
        $createdGoods = $this->createGoods();
        $createdGoods = $createdGoods[0];

        $goods = $this->mockGoods();
        $good = $goods[0];
        unset($good['min_amount']);

        $this->assertArrayEquals($good, $createdGoods, array_keys($good));
    }

    public function testGetGoods()
    {
        $createdGoods = $this->createGoods();
        $createdGood = $createdGoods[0];

        $gettedGoods = $this->getGoodsService()->getGoods($createdGood['id']);
        $this->assertArrayEquals($createdGood, $gettedGoods);
    }

    public function testUpdateGoods()
    {
        $createdGoods = $this->createGoods();
        $createdGoods = $createdGoods[0];

        $fields = array(
            'id' => $createdGoods['id'],
            'sale_price' => '40',
            'min_amount' => 1,
        );

        $updateGoods = $this->getGoodsService()->updateGoods($createdGoods['id'], $fields);

        $this->assertEquals($fields['sale_price'], $updateGoods['sale_price']);
    }

    public function testDeleteGoods()
    {
        $createdGoods = $this->createGoods();
        $createdGoods = $createdGoods[0];

        $this->getGoodsService()->deleteGoods($createdGoods['id']);

        $deleteGoods = $this->getGoodsService()->getGoods($createdGoods['id']);

        $this->assertEquals(1, $deleteGoods['is_deleted']);
    }

    public function testUndeleteGoods()
    {
        $createdGoods = $this->createGoods();
        $createdGoods = $createdGoods[0];

        $this->getGoodsService()->undeleteGoods($createdGoods['id']);

        $deleteGoods = $this->getGoodsService()->getGoods($createdGoods['id']);

        $this->assertEquals(0, $deleteGoods['is_deleted']);
    }

    /**
     * @expectedException \Biz\Common\Exception\NotFoundException
     */
    public function testDeleteNotExistGoods()
    {
        $this->getGoodsService()->deleteGoods(-1);
    }

    public function testIsGoodsSpellCodeAvailable()
    {
        $createdGoods = $this->createGoods();
        $createdGoods = $createdGoods[0];

        $avaliable = $this->getGoodsService()->isGoodsSpellCodeAvailable($createdGoods['spell_code']);
        $this->assertEquals(false, $avaliable);

        $avaliable = $this->getGoodsService()->isGoodsSpellCodeAvailable('this_is_a_avaliable_code');
        $this->assertEquals(true, $avaliable);
    }

    public function testsearchGoods()
    {
        $this->createGoods();

        $conditions = array('category_id' => '2');
        $orderBy = array('id' => 'ASC');

        $goods = $this->getGoodsService()->searchGoods($conditions, $orderBy, 0, 5);
        $this->assertEquals(4, count($goods));
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
            array('id' => 1, 'name' => '草莓蛋糕', 'small_picture' => '', 'min_amount' => '0',
                'category_id' => '2', 'spec' => '500g一盒', 'unit' => '盒', 'ingredient' => '草莓、牛油、蛋糕',
                'sale_price' => '12', 'cost_price' => '12', 'group_code' => 'product', 'provider_id' => '3', ),
            array('id' => 2, 'name' => '榴莲蛋糕', 'small_picture' => '', 'min_amount' => '0',
                'category_id' => '2', 'spec' => '500g一盒', 'unit' => '盒', 'ingredient' => '榴莲、牛油、蛋糕',
                'sale_price' => '12', 'group_code' => 'product', 'provider_id' => '3', ),
            array('id' => 3, 'name' => '香蕉蛋糕', 'small_picture' => '', 'min_amount' => '0',
                'category_id' => '2', 'spec' => '500g一盒', 'unit' => '盒', 'ingredient' => '香蕉、牛油、蛋糕',
                'sale_price' => '12', 'group_code' => 'product', 'provider_id' => '3', ),
            array('id' => 4, 'name' => '蓝莓蛋糕', 'small_picture' => '', 'min_amount' => '0',
                'category_id' => '2', 'spec' => '500g一盒', 'unit' => '盒', 'ingredient' => '蓝莓、牛油、蛋糕',
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
}
