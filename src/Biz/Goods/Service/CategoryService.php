<?php

namespace Biz\Goods\Service;

interface CategoryService
{
    public function getCategory($id);

    public function findCategoriesByIds(array $ids);

    public function findCategoriesByParentId($parentId);

    public function createCategory(array $category);

    public function updateCategory($id, array $fields);

    public function deleteCategory($id);

    public function findCategoriesByGroupAndOwner($groupCode, $ownerId);
}
