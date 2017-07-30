<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\Exception\NotFoundException;

class StockController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $user = $this->getCurrentUser();

        $defaultConditions = array(
            'owner_id' => $user->getParentId(),
            'is_deleted' => 0,
        );

        $conditions = array_merge($defaultConditions, $conditions);

        $paginator = new Paginator(
            $request,
            $this->getStockService()->countStocks($conditions),
            20
        );

        $stocks = $this->getStockService()->searchStocks(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $goodsIds = ArrayToolkit::column($stocks, 'goods_id');
        $goodsList = $this->getGoodsService()->findGoodsByIds($goodsIds);

        $categoryIds = ArrayToolkit::column($stocks, 'category_id');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        $providerIds = ArrayToolkit::column($goodsList, 'provider_id');
        $providers = $this->getUserService()->findUsersByIds($providerIds);

        $aliasIds = ArrayToolkit::column($goodsList, 'owner_id');
        $aliases = $this->getUserService()->findUsersByIds($aliasIds);

        $todos = $this->getStockService()->findToDosByTargetTypeAndCreatorId('goods', $user->getId());
        $todoTargetIds = ArrayToolkit::column($todos, 'target_id');

        return $this->render('AppBundle:stock:index.html.twig', array(
            'stocks' => $stocks,
            'goods_list' => $goodsList,
            'categories' => $categories,
            'providers' => $providers,
            'aliases' => $aliases,
            'todo_target_ids' => $todoTargetIds,
            'paginator' => $paginator,
        ));
    }

    public function createAction(Request $request)
    {
        $goodsId = $request->query->get('goods_id');
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $goods = $this->getGoodsService()->getGoods($fields['goods_id']);
            $stock = array(
                'goods_id' => $goods['id'],
                'name' => $goods['name'],
                'group_code' => $goods['group_code'],
                'category_id' => $goods['category_id'],
                'provider_id' => $goods['provider_id'],
                'min_amount' => $fields['min_amount'],
                'amount' => 0,
            );
            $this->getStockService()->createStock($stock);

            return $this->createJsonResponse(true);
        }

        $goods = $this->getGoodsService()->getGoods($goodsId);
        $stock = array(
            'id' => 0,
            'name' => $goods['name'],
            'goods_id' => $goods['id'],
            'min_amount' => 0,
        );

        return $this->render('AppBundle:stock:modal.html.twig', array(
            'stock' => $stock,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $stock = $this->getStockService()->getStock($id);

        if (empty($stock)) {
            throw new NotFoundException(sprintf('stock id#%s not found', $id));
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $this->getStockService()->updateStock($id, $fields);

            return $this->createJsonResponse(true);
        }

        return $this->render('AppBundle:stock:modal.html.twig', array(
            'stock' => $stock,
        ));
    }

    public function createRecordAction(Request $request, $type)
    {
        $user = $this->getCurrentUser();

        $todos = $this->getStockService()->findToDosByTargetTypeAndCreatorId('goods', $user->getId());

        $goodsIds = ArrayToolkit::column($todos, 'target_id');
        $goodsList = $this->getGoodsService()->findGoodsByIds($goodsIds);

        $stocks = $this->getStockService()->findStocksByGoodsIds($goodsIds);

        $categoryIds = ArrayToolkit::column($goodsList, 'category_id');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        $providerIds = ArrayToolkit::column($goodsList, 'provider_id');
        $providers = $this->getUserService()->findUsersByIds($providerIds);

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $stockRecords = $this->extractArrayFields($fields, array('goods_id', 'wave_amount', 'cost_price', 'remark', 'type'));

            //转换wave_amount
            foreach ($stockRecords as &$stockRecord) {
                if (in_array($stockRecord['type'], array('out', 'loss'))) {
                    $stockRecord['wave_amount'] = -$stockRecord['wave_amount'];
                } elseif ($stockRecord['type'] == 'check') {
                    $stockRecord['wave_amount'] = $stockRecord['wave_amount'] - $stocks[$stockRecord['goods_id']]['amount'];
                }
            }

            $this->getStockService()->batchCreateStockRecord($stockRecords);

            foreach ($todos as $todo) {
                $this->getStockService()->deleteToDo($todo['id']);
            }

            return $this->createJsonResponse(true);
        }

        return $this->render('AppBundle:stock:modal-record.html.twig', array(
            'stocks' => $stocks,
            'goods_list' => $goodsList,
            'categories' => $categories,
            'providers' => $providers,
            'type' => $type,
        ));
    }

    public function showRecordAction(Request $request, $goodsId)
    {
        $goods = $this->getGoodsService()->getGoods($goodsId);

        $conditions = array('goods_id' => $goodsId);

        $paginator = new Paginator(
            $request,
            $this->getStockService()->countStockRecords($conditions),
            15
        );

        $stockRecords = $this->getStockService()->searchStockRecords(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $operatorIds = ArrayToolkit::column($stockRecords, 'operator_id');
        $operators = $this->getUserService()->findUsersByIds($operatorIds);

        $ownerIds = ArrayToolkit::column($stockRecords, 'owner_id');
        $owners = $this->getUserService()->findUsersByIds($ownerIds);

        return $this->render('AppBundle:stock:show-record.html.twig', array(
            'goods' => $goods,
            'stock_records' => $stockRecords,
            'operators' => $operators,
            'owners' => $owners,
            'paginator' => $paginator,
        ));
    }

    public function ajaxGoodsSearchAction(Request $request)
    {
        $keyword = $request->query->get('keyword');

        $user = $this->getCurrentUser();

        $conditions = array(
            'name' => $keyword,
            'region_id' => $user['region_id'],
            'is_deleted' => 0,
        );

        $goodsList = $this->getGoodsService()->searchGoods($conditions, array('created_time' => 'DESC'), 0, 20);

        //去掉已经存在库存的商品
        $goodsIds = ArrayToolkit::column($goodsList, 'id');
        $stocks = $this->getStockService()->findStocksByGoodsIdsAndOwnerId($goodsIds, $user->getParentId());
        $goodsIds = ArrayToolkit::column($stocks, 'goods_id');

        $providerIds = ArrayToolkit::column($goodsList, 'provider_id');
        $providers = $this->getUserService()->findUsersByIds($providerIds);

        foreach ($goodsList as $key => &$goods) {
            if (in_array($goods['id'], $goodsIds)) {
                unset($goodsList[$key]);
            } else {
                $goods = ArrayToolkit::parts($goods, array('id', 'name', 'provider_id', 'group_code', 'unit', 'sale_price'));
                if ($goods['group_code'] == 'product') {
                    $goods['provider'] = ArrayToolkit::parts($providers[$goods['provider_id']], array('id', 'nickname'));
                }
            }
        }

        return $this->createJsonCleanResponse($goodsList);
    }

    protected function extractArrayFields(array $fields, array $keys)
    {
        $extracted = array();

        foreach ($keys as $key) {
            foreach ($fields[$key] as $index => $value) {
                $extracted[$index][$key] = $value;
            }
        }

        return $extracted;
    }

    /**
     * @return \Biz\Stock\Service\StockService
     */
    protected function getStockService()
    {
        return $this->createService('Stock:StockService');
    }

    /**
     * @return \Biz\Goods\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Goods:CategoryService');
    }

    /**
     * @return \Biz\Goods\Service\GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return \Biz\GoodsSet\Service\GoodsSetService
     */
    protected function getGoodsSetService()
    {
        return $this->createService('GoodsSet:GoodsSetService');
    }
}
