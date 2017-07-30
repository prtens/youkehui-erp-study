<?php

namespace AppBundle\Extension\DataTag;

class CategoriesDataTag extends BaseDataTag
{
    /**
     * 获取分类.
     *
     * @param array $arguments = array(
     *                         'group_code' => ''
     *                         'owner_id' => ''
     *                         )
     *
     * @return array
     */
    public function getData(array $arguments)
    {
        $groupCode = $arguments['group_code'];
        $ownerId = $arguments['owner_id'];

        $categories = $this->getCategoryService()->findCategoriesByGroupAndOwner($groupCode, $ownerId);

        return $categories;
    }

    /**
     * @return \Biz\Goods\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Goods:CategoryService');
    }
}
