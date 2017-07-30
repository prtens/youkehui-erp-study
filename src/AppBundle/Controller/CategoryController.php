<?php

namespace AppBundle\Controller;

use Biz\Common\Exception\NotFoundException;
use Biz\Common\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class CategoryController extends BaseController
{
    public function indexAction(Request $request, $groupCode)
    {
        $user = $this->getCurrentUser();

        $categories = $this->getCategoryService()->findCategoriesByGroupAndOwner($groupCode, $user->getParentId());
        $categories = ArrayToolkit::flatToTree($categories, 0);

        return $this->render('AppBundle:category:index.html.twig', array(
            'categories' => $categories,
            'group_code' => $groupCode,
        ));
    }

    public function createAction(Request $request)
    {
        $groupCode = $request->query->get('group_code', 'product');
        $parentId = $request->query->get('parent_id', 0);

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $category = $this->getCategoryService()->createCategory($fields);

            return $this->renderTbody($category['group_code']);
        }

        $defaultCategory = array(
            'id' => 0,
            'name' => '',
            'seq' => 0,
            'group_code' => $groupCode,
            'parent_id' => $parentId,
        );

        return $this->render('AppBundle:category:modal.html.twig', array(
            'category' => $defaultCategory,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);

        if (empty($category)) {
            throw new NotFoundException(sprintf('category id#%s not found', $id));
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $category = $this->getCategoryService()->updateCategory($id, $fields);

            return $this->renderTbody($category['group_code']);
        }

        return $this->render('AppBundle:category:modal.html.twig', array(
            'category' => $category,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);

        if (empty($category)) {
            throw new NotFoundException(sprintf('category id#%s not found', $id));
        }

        if ($this->getGoodsService()->countGoods(array('category_id' => $id)) > 0) {
            return $this->createJsonResponse('无法删除，该分类正在使用中，请先将产品或设备移除该分类后重试');
        }

        $children = $this->getCategoryService()->findCategoriesByParentId($category['id']);
        if (count($children) > 0) {
            throw new RuntimeException(sprintf('category id#%s delete failed, please remove its children first', $id));
        }

        $this->getCategoryService()->deleteCategory($id);

        return $this->renderTbody($category['group_code']);
    }

    protected function renderTbody($groupCode)
    {
        $user = $this->getCurrentUser();

        $categories = $this->getCategoryService()->findCategoriesByGroupAndOwner($groupCode, $user->getParentId());
        $categories = ArrayToolkit::flatToTree($categories, 0);

        return $this->render('AppBundle:category:tbody.html.twig', array(
            'categories' => $categories,
        ));
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
}
