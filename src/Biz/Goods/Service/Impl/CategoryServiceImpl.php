<?php

namespace Biz\Goods\Service\Impl;

use Biz\BaseService;
use Biz\Goods\Service\CategoryService;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\Exception\NotFoundException;
use Biz\Common\Exception\InvalidArgumentException;
use Biz\Common\Exception\AccessDeniedException;

class CategoryServiceImpl extends BaseService implements CategoryService
{
    public function getCategory($id)
    {
        return $this->getCategoryDao()->get($id);
    }

    public function findCategoriesByParentId($parentId)
    {
        return $this->getCategoryDao()->findByParentId($parentId);
    }

    public function findCategoriesByIds(array $ids)
    {
        return ArrayToolkit::index($this->getCategoryDao()->findByIds($ids), 'id');
    }

    public function createCategory(array $category)
    {
        $category = $this->filterCreateCategoryFields($category);
        $category = $this->getCategoryDao()->create($category);

        $categoryType = $category['group_code'] == 'product' ? '产品' : '设备';
        $this->getLogService()->info('category', 'create', "新增{$categoryType}分类-{$category['name']}(#{$category['id']})", $category);

        return $category;
    }

    public function updateCategory($id, array $fields)
    {
        $category = $this->getCategory($id);

        if (empty($category)) {
            throw new NotFoundException(sprintf('category id#%s not found', $id));
        }

        $user = $this->getCurrentUser();
        if ($category['owner_id'] != $user->getParentId()) {
            throw new AccessDeniedException();
        }

        $fields = $this->filterUpdateCategoryFields($fields);
        $category = $this->getCategoryDao()->update($id, $fields);

        $categoryType = $category['group_code'] == 'product' ? '产品' : '设备';
        $this->getLogService()->info('category', 'update', "更新{$categoryType}分类-{$category['name']}(#{$category['id']})", $category);

        return $category;
    }

    public function deleteCategory($id)
    {
        $category = $this->getCategory($id);

        if (empty($category)) {
            throw new NotFoundException(sprintf('category id#%s not found', $id));
        }

        $user = $this->getCurrentUser();
        if ($category['owner_id'] != $user->getParentId()) {
            throw new AccessDeniedException();
        }
        $this->getCategoryDao()->delete($id);

        $categoryType = $category['group_code'] == 'product' ? '产品' : '设备';
        $this->getLogService()->info('category', 'delete', "删除{$categoryType}分类-{$category['name']}(#{$category['id']})");
    }

    public function searchCategories($conditions, $orderBy, $start, $limit)
    {
        return $this->getCategoryDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countCategories($conditions)
    {
        return $this->getCategoryDao()->count($conditions);
    }

    public function findCategoriesByGroupAndOwner($groupCode, $ownerId)
    {
        return $this->getCategoryDao()->findByGroupCodeAndOwnerId($groupCode, $ownerId);
    }

    protected function filterCreateCategoryFields($fields)
    {
        if (!ArrayToolkit::requires($fields, array('name', 'parent_id', 'group_code'))) {
            throw new InvalidArgumentException('Missing required fields when creating category');
        }

        $fields = ArrayToolkit::parts($fields, array(
            'id',
            'name',
            'parent_id',
            'group_code',
            'owner_id',
            'seq',
        ));

        $user = $this->getCurrentUser();

        $fields['owner_id'] = $user->getParentId();
        if ($fields['parent_id'] == 0) {
            $fields['depth'] = 1;
        } else {
            $parentCategory = $this->getCategory($fields['parent_id']);
            if (empty($parentCategory)) {
                throw new NotFoundException(sprintf('category id#%s not found', $fields['parent_id']));
            }
            $fields['depth'] = (int) $parentCategory['depth'] + 1;
        }

        $fields['seq'] = 0;

        return $fields;
    }

    protected function filterUpdateCategoryFields($fields)
    {
        // 只保留允许更新的字段
        $fields = ArrayToolkit::parts($fields, array(
            'name',
            'seq',
        ));

        return $fields;
    }

    /**
     * @return \Biz\Goods\Dao\CategoryDao
     */
    protected function getCategoryDao()
    {
        return $this->createDao('Goods:CategoryDao');
    }

    /**
     * @return \Biz\System\Service\LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return \Biz\Goods\Service\GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }
}
