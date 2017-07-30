<?php

namespace Tests\Goods\Service;

use Biz\BaseTestCase;

class CategoryServiceTest extends BaseTestCase
{
    public function testCreateCategory()
    {
        $createdCategories = $this->createCategories();
        $createdCategory = $createdCategories[0];

        $categories = $this->mockCategories();
        $category = $categories[0];

        $this->assertArrayEquals($category, $createdCategory, array_keys($category));
    }

    public function testGetCategory()
    {
        $createdCategories = $this->createCategories();
        $createdCategory = $createdCategories[0];

        $gettedCategory = $this->getCategoryService()->getCategory($createdCategory['id']);
        $this->assertArrayEquals($createdCategory, $gettedCategory);
    }

    public function testUpdateCategory()
    {
        $createdCategories = $this->createCategories();
        $createdCategory = $createdCategories[0];

        $fields = array(
            'name' => '修改分类1',
        );

        $updateCategory = $this->getCategoryService()->updateCategory($createdCategory['id'], $fields);

        $this->assertArrayEquals(array_merge($createdCategory, $fields), $updateCategory);
    }

    public function testDeleteCategory()
    {
        $createdCategories = $this->createCategories();
        $createdCategory = $createdCategories[0];

        $this->getCategoryService()->deleteCategory($createdCategory['id']);

        $deleteCategory = $this->getCategoryService()->getCategory($createdCategory['id']);

        $this->assertEquals(null, $deleteCategory);
    }

    /**
     * @expectedException \Biz\Common\Exception\NotFoundException
     */
    public function testDeleteNotExistCategory()
    {
        $this->getCategoryService()->deleteCategory(-1);
    }

    public function testFindByGroupCodeAndOwnerId()
    {
        $this->createCategories();
        $groupCode = 'product';

        $user = $this->getCurrentUser();
        $findedCategories = $this->getCategoryService()->findCategoriesByGroupAndOwner($groupCode, $user['id']);

        $this->assertEquals(10, count($findedCategories));
    }

    protected function createCategories()
    {
        $categories = $this->mockCategories();

        $createdCategories = array();

        foreach ($categories as $key => $category) {
            $createdCategories[$key] = $this->getCategoryService()->createCategory($category);
        }

        return $createdCategories;
    }

    protected function mockCategories()
    {
        $categories = array(
            array('id' => 1, 'name' => '分类1', 'parent_id' => 0, 'group_code' => 'product'),
            array('id' => 2, 'name' => '分类2', 'parent_id' => 0, 'group_code' => 'product'),
            array('id' => 3, 'name' => '分类3', 'parent_id' => 0, 'group_code' => 'product'),
            array('id' => 4, 'name' => '分类1-1', 'parent_id' => 1, 'group_code' => 'product'),
            array('id' => 5, 'name' => '分类1-2', 'parent_id' => 1, 'group_code' => 'product'),
            array('id' => 6, 'name' => '分类1-3', 'parent_id' => 1, 'group_code' => 'product'),
            array('id' => 7, 'name' => '分类2-1', 'parent_id' => 2, 'group_code' => 'product'),
            array('id' => 8, 'name' => '分类2-2', 'parent_id' => 2, 'group_code' => 'product'),
            array('id' => 9, 'name' => '分类2-3', 'parent_id' => 2, 'group_code' => 'product'),
            array('id' => 10, 'name' => '分类1-1-1', 'parent_id' => 4, 'group_code' => 'product'),
        );

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
