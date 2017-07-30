<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\FileToolkit;
use Biz\Common\Exception\NotFoundException;
use Biz\Common\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use AppBundle\Common\PinyinToolkit;

class GoodsController extends BaseController
{
    public function indexAction(Request $request, $groupCode)
    {
        $conditions = $request->query->all();

        if (!empty($conditions['category_id'])) {
            $children = $this->getCategoryService()->findCategoriesByParentId($conditions['category_id']);
            if (!empty($children)) {
                $childrenIds = ArrayToolkit::column($children, 'id');

                array_push($childrenIds, $conditions['category_id']);
                $conditions['category_ids'] = $childrenIds;
                unset($conditions['category_id']);
            }
        }

        $user = $this->getCurrentUser();
        $defaultConditions = array(
            'owner_region_id' => $user['region_id'],
            'is_deleted' => 0,
            'group_code' => $groupCode,
        );

        //设备不应该让其他加盟商看到
        if ($groupCode == 'facility') {
            $defaultConditions['owner_id'] = $user->getParentId();
        }

        $conditions = array_merge($defaultConditions, $conditions);

        $paginator = new Paginator(
            $request,
            $this->getGoodsService()->countGoods($conditions),
            20
        );

        $goodsList = $this->getGoodsService()->searchGoods(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $goodsIds = ArrayToolkit::column($goodsList, 'id');
        $stocks = $this->getStockService()->findStocksByGoodsIdsAndOwnerId($goodsIds, $user->getParentId());
        $existedGoodsIds = ArrayToolkit::column($stocks, 'goods_id');

        $categoryIds = ArrayToolkit::column($goodsList, 'category_id');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        $providerIds = ArrayToolkit::column($goodsList, 'provider_id');
        $providers = $this->getUserService()->findUsersByIds($providerIds);

        $aliasIds = ArrayToolkit::column($goodsList, 'owner_id');
        $aliases = $this->getUserService()->findUsersByIds($aliasIds);

        return $this->render("AppBundle:goods:{$groupCode}.html.twig", array(
            'goods_list' => $goodsList,
            'categories' => $categories,
            'providers' => $providers,
            'aliases' => $aliases,
            'existed_goods_ids' => $existedGoodsIds,
            'group_code' => $groupCode,
            'paginator' => $paginator,
        ));
    }

    public function ajaxSearchAction(Request $request)
    {
        $keyword = $request->query->get('keyword');
        $groupCode = $request->query->get('group_code');

        $user = $this->getCurrentUser();

        $conditions = array(
            'name' => $keyword,
            'group_code' => $groupCode,
            'owner_id' => $user->getParentId(),
            'region_id' => $user['region_id'],
        );

        $goodsList = $this->getGoodsService()->searchGoods($conditions, array('created_time' => 'DESC'), 0, 20);

        $providerIds = ArrayToolkit::column($goodsList, 'provider_id');
        $providers = $this->getUserService()->findUsersByIds($providerIds);

        $aliasIds = ArrayToolkit::column($goodsList, 'owner_id');
        $aliases = $this->getUserService()->findUsersByIds($aliasIds);

        foreach ($goodsList as &$goods) {
            $goods = ArrayToolkit::parts($goods, array('id', 'name', 'provider_id', 'owner_id', 'group_code', 'spec', 'unit', 'sale_price'));
            if ($goods['group_code'] == 'product') {
                $goods['provider'] = ArrayToolkit::parts($providers[$goods['provider_id']], array('id', 'nickname'));
            } else {
                $goods['alias'] = ArrayToolkit::parts($aliases[$goods['owner_id']], array('id', 'nickname'));
            }
        }

        return $this->createJsonCleanResponse($goodsList);
    }

    public function createAction(Request $request)
    {
        $groupCode = $request->query->get('group_code', 'product');

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $coverImage = $request->files->get('cover_image');

            $fields['spell_code'] = PinyinToolkit::convert($fields['name']);

            if ($coverImage) {
                $this->checkImage($coverImage);
                $filePaths = $this->getUploadFileService()->uploadCoverImage($coverImage);

                $fields['small_picture'] = $filePaths['small'];
                $fields['medium_picture'] = $filePaths['medium'];
                $fields['large_picture'] = $filePaths['large'];
            }

            $this->getGoodsService()->createGoods($fields);

            return $this->createJsonResponse(true);
        }

        $defaultGoods = array(
            'id' => 0,
            'name' => '',
            'spell_code' => '',
            'category_id' => 0,
            'provider_id' => 0,
            'cost_price' => '0.00',
            'sale_price' => '0.00',
            'spec' => '',
            'min_amount' => '0',
            'unit' => '',
            'ingredient' => '',
            'about' => '',
            'group_code' => $groupCode,
        );

        return $this->render("AppBundle:goods:modal-{$groupCode}.html.twig", array(
            'goods' => $defaultGoods,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $goods = $this->getGoodsService()->getGoods($id);

        if (empty($goods)) {
            throw new NotFoundException(sprintf('goods id#%s not found', $id));
        }

        $groupCode = $goods['group_code'];

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $coverImage = $request->files->get('cover_image');

            $fields['spell_code'] = PinyinToolkit::convert($fields['name']);

            if ($coverImage) {
                $this->checkImage($coverImage);
                $filePaths = $this->getUploadFileService()->uploadCoverImage($coverImage);

                $fields['small_picture'] = $filePaths['small'];
                $fields['medium_picture'] = $filePaths['medium'];
                $fields['large_picture'] = $filePaths['large'];
            }
            $this->getGoodsService()->updateGoods($id, $fields);

            return $this->createJsonResponse(true);
        }
        $goods['min_amount'] = $this->getStockService()->getStockByGoodsId($goods['id'])['min_amount'];

        return $this->render("AppBundle:goods:modal-{$groupCode}.html.twig", array(
            'goods' => $goods,
        ));
    }

    public function showAction(Request $request, $id)
    {
        $goods = $this->getGoodsService()->getGoods($id);

        if (empty($goods)) {
            throw new NotFoundException(sprintf('goods id#%s not found', $id));
        }

        $category = $this->getCategoryService()->getCategory($goods['category_id']);
        $provider = $this->getUserService()->getUser($goods['provider_id']);
        $alias = $this->getUserService()->getUser($goods['owner_id']);
        $stock = $this->getStockService()->getStockByGoodsId($goods['id']);

        return $this->render('AppBundle:goods:show.html.twig', array(
            'goods' => $goods,
            'category' => $category,
            'provider' => $provider,
            'alias' => $alias,
            'stock' => $stock,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $goods = $this->getGoodsService()->getGoods($id);

        if (empty($goods)) {
            throw new NotFoundException(sprintf('goods id#%s not found', $id));
        }

        if (!$this->getGoodsService()->canDeleteGoods($id)) {
            //JsonResponse或者RuntimeException都可以
            return $this->createJsonResponse(null, sprintf('无法删除，"%s"已经被引用，请先对其取消套餐等功能的引用后重试', $goods['name']), 500);
        }

        $this->getGoodsService()->deleteGoods($id);

        return $this->createJsonResponse(true);
    }

    public function trueDeleteAction(Request $request, $id)
    {
        $goods = $this->getGoodsService()->getGoods($id);

        if (empty($goods)) {
            throw new NotFoundException(sprintf('goods id#%s not found', $id));
        }

        if (!$this->getGoodsService()->canDeleteGoods($id)) {
            throw new RuntimeException(sprintf('无法删除，"%s"已经被引用，请先对其取消套餐等功能的引用后重试', $goods['name']));
        }

        $this->getGoodsService()->trueDeleteGoods($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids');

        foreach ($ids as $id) {
            $goods = $this->getGoodsService()->getGoods($id);

            if (empty($goods)) {
                throw new NotFoundException(sprintf('goods id#%s not found', $id));
            }
            $this->getGoodsService()->deleteGoods($id);
        }

        return $this->createJsonResponse(true);
    }

    public function validateSpellCodeAction(Request $request)
    {
        $exclude = $request->query->get('exclude');
        $spellCode = $request->request->get('spell_code');

        $available = $exclude == $spellCode ? true : $this->getGoodsService()->isGoodsSpellCodeAvailable($spellCode);

        if ($available) {
            return $this->createJsonCleanResponse(true);
        }

        return $this->createJsonCleanResponse('拼音编码已被占用，请重新输入');
    }

    public function trashAction(Request $request, $groupCode)
    {
        $conditions = $request->query->all();

        $user = $this->getCurrentUser();
        $defaultConditions = array(
            'owner_region_id' => $user['region_id'],
            'is_deleted' => 1,
            'group_code' => $groupCode,
        );
        $conditions = array_merge($defaultConditions, $conditions);

        $paginator = new Paginator(
            $request,
            $this->getGoodsService()->countGoods($conditions),
            20
        );
        $deletedGoods = $this->getGoodsService()->searchGoods(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categoryIds = ArrayToolkit::column($deletedGoods, 'category_id');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        $providerIds = ArrayToolkit::column($deletedGoods, 'provider_id');
        $providers = $this->getUserService()->findUsersByIds($providerIds);

        $aliasIds = ArrayToolkit::column($deletedGoods, 'owner_id');
        $aliases = $this->getUserService()->findUsersByIds($aliasIds);

        return $this->render("AppBundle:goods:trash-{$groupCode}.html.twig", array(
            'deleted_goods' => $deletedGoods,
            'categories' => $categories,
            'providers' => $providers,
            'aliases' => $aliases,
            'paginator' => $paginator,
            'group_code' => $groupCode,
        ));
    }

    public function undeleteAction($id)
    {
        $goods = $this->getGoodsService()->getGoods($id);

        if (empty($goods)) {
            throw new NotFoundException(sprintf('goods id#%s not found', $id));
        }

        $this->getGoodsService()->undeleteGoods($id);

        return $this->createJsonResponse(true);
    }

    protected function checkImage(UploadedFile $image)
    {
        if (!FileToolkit::isImageFile($image)) {
            throw new RuntimeException(sprintf('上传的图片类型只支持：%s', FileToolkit::getImageExtensions()));
        }

        if (!FileToolkit::validateFileMaxSize($image, 5 * 1024 * 1024)) {
            throw new RuntimeException('上传的图片不能超过5M');
        }
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
     * @return \Biz\File\Service\UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
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

    /**
     * @return \Biz\Order\Service\OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
