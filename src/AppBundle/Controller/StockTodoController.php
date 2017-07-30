<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\Exception\NotFoundException;
use Biz\Common\Exception\AccessDeniedException;

class StockTodoController extends BaseController
{
    public function goodsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $todos = $this->getStockService()->findToDosByTargetTypeAndCreatorId('goods', $user->getId());

        $goodsIds = ArrayToolkit::column($todos, 'target_id');
        $goodsList = $this->getGoodsService()->findGoodsByIds($goodsIds);

        $categoryIds = ArrayToolkit::column($goodsList, 'category_id');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        $providerIds = ArrayToolkit::column($goodsList, 'provider_id');
        $providers = $this->getUserService()->findUsersByIds($providerIds);

        return $this->render('AppBundle:todo-list:goods.html.twig', array(
            'todos' => $todos,
            'goods_list' => $goodsList,
            'categories' => $categories,
            'providers' => $providers,
        ));
    }

    public function goodsSetAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $todos = $this->getStockService()->findToDosByTargetTypeAndCreatorId('goods-set', $user->getId());

        $goodsSetIds = ArrayToolkit::column($todos, 'target_id');
        $goodsSets = $this->getGoodsSetService()->findGoodsSetsByIds($goodsSetIds);

        foreach ($goodsSets as &$goodsSet) {
            $goodsSet['max_amount'] = $this->getGoodsSetService()->calculateAvailableAmount($goodsSet['id']);
        }

        return $this->render('AppBundle:todo-list:goods-set.html.twig', array(
            'todos' => $todos,
            'goods_sets' => $goodsSets,
        ));
    }

    public function addAction(Request $request)
    {
        $fields = $request->request->all();

        $this->getStockService()->createToDo($fields);

        return $this->createJsonCleanResponse(true);
    }

    public function deleteAction(Request $request, $id)
    {
        $todo = $this->getStockService()->getToDo($id);

        if (empty($todo)) {
            throw new NotFoundException(sprintf('todo id#%s not found', $id));
        }

        $currentUser = $this->getCurrentUser();

        if ($todo['creator_id'] != $currentUser->getId()) {
            throw new AccessDeniedException('Access Denied');
        }

        $this->getStockService()->deleteToDo($id);

        return $this->createJsonCleanResponse(true);
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
     * @return \Biz\GoodsSet\Service\GoodsSetService
     */
    protected function getGoodsSetService()
    {
        return $this->createService('GoodsSet:GoodsSetService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
