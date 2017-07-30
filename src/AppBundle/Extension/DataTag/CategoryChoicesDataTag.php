<?php

namespace AppBundle\Extension\DataTag;

use AppBundle\Common\ArrayToolkit;

class CategoryChoicesDataTag extends BaseDataTag
{
    /**
     * 获取分类.
     *
     * @param array $arguments = array(
     *                         'group_code' => ''
     *                         'owner_id' => ''
     *                         'indent' => ' ' //缩进符号，默认空格
     *                         )
     *
     * @return array
     */
    public function getData(array $arguments)
    {
        $groupCode = $arguments['group_code'];
        $ownerId = $arguments['owner_id'];
        $indent = isset($arguments['indent']) ? $arguments['indent'] : '　';

        $choices = array();

        $categories = $this->getCategoryService()->findCategoriesByGroupAndOwner($groupCode, $ownerId);

        //排序
        $categories = ArrayToolkit::flatToTree($categories, 0);
        $categories = ArrayToolkit::treeToFlat($categories);

        foreach ($categories as $category) {
            $choices[$category['id']] = str_repeat($indent, ($category['depth'] - 1)).$category['name'];
        }

        return $choices;
    }

    /**
     * @return \Biz\Goods\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Goods:CategoryService');
    }
}
