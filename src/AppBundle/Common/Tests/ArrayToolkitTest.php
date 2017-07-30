<?php

namespace AppBundle\Common\Tests;

use Biz\BaseTestCase;
use AppBundle\Common\ArrayToolkit;

class ArrayTookitTest extends BaseTestCase
{
    public function testFlatToTree()
    {
        $tree = ArrayToolkit::flatToTree($this->mockFlatArrays(), 0);
        $this->assertArrayEquals($this->mockTree(), $tree);

        $tree2 = ArrayToolkit::flatToTree($this->mockFlatArrays2(), '');
        $this->assertArrayEquals($this->mockFlatArrays2ToTree(), $tree2);
    }

    public function testTreeToFlat()
    {
        $flatArray = ArrayToolkit::treeToFlat($this->mockTree());
        $this->assertArrayEquals($this->mockFlatArrays(), $flatArray);

        $flatArray2 = ArrayToolkit::treeToFlat($this->mockTree2());
        $this->assertArrayEquals($this->mockTree2ToFlat(), $flatArray2);
    }

    public function testTreeToFlatToTree()
    {
        $flatArray = ArrayToolkit::treeToFlat($this->mockTree());
        $tree = ArrayToolkit::flatToTree($flatArray, 0);
        $this->assertArrayEquals($this->mockTree(), $tree);
    }

    protected function mockFlatArrays()
    {
        return array(
            array(
                'id' => 1,
                'name' => 'A',
                'parent_id' => 0,
            ),
            array(
                'id' => 11,
                'name' => 'A-A',
                'parent_id' => 1,
            ),
            array(
                'id' => 111,
                'name' => 'A-A-A',
                'parent_id' => 11,
            ),
            array(
                'id' => 112,
                'name' => 'A-A-B',
                'parent_id' => 11,
            ),
            array(
                'id' => 12,
                'name' => 'A-B',
                'parent_id' => 1,
            ),
            array(
                'id' => 2,
                'name' => 'B',
                'parent_id' => 0,
            ),
            array(
                'id' => 21,
                'name' => 'B-A',
                'parent_id' => 2,
            ),
            array(
                'id' => 22,
                'name' => 'B-B',
                'parent_id' => 2,
            ),
        );
    }

    protected function mockTree()
    {
        return array(
            array(
                'id' => 1,
                'name' => 'A',
                'parent_id' => 0,
                'children' => array(
                    array(
                        'id' => 11,
                        'name' => 'A-A',
                        'parent_id' => 1,
                        'children' => array(
                            array(
                                'id' => 111,
                                'name' => 'A-A-A',
                                'parent_id' => 11,
                            ),
                            array(
                                'id' => 112,
                                'name' => 'A-A-B',
                                'parent_id' => 11,
                            ),
                        ),
                    ),
                    array(
                        'id' => 12,
                        'name' => 'A-B',
                        'parent_id' => 1,
                    ),
                ),
            ),
            array(
                'id' => 2,
                'name' => 'B',
                'parent_id' => 0,
                'children' => array(
                    array(
                        'id' => 21,
                        'name' => 'B-A',
                        'parent_id' => 2,
                    ),
                    array(
                        'id' => 22,
                        'name' => 'B-B',
                        'parent_id' => 2,
                    ),
                ),
            ),
        );
    }

    protected function mockFlatArrays2()
    {
        return array(
            'codeA' => array(
                'name' => 'A',
                'parent_id' => '',
            ),
            'codeAA' => array(
                'name' => 'A-A',
                'parent_id' => 'codeA',
            ),
            'codeB' => array(
                'name' => 'B',
                'parent_id' => '',
            ),
        );
    }

    protected function mockFlatArrays2ToTree()
    {
        return array(
            'codeA' => array(
                'name' => 'A',
                'children' => array(
                    'codeAA' => array(
                        'name' => 'A-A',
                        'parent_id' => 'codeA',
                        'id' => 'codeAA',
                    ),
                ),
                'parent_id' => '',
                'id' => 'codeA',
            ),
            'codeB' => array(
                'name' => 'B',
                'parent_id' => '',
                'id' => 'codeB',
            ),
        );
    }

    protected function mockTree2()
    {
        return array(
            'codeA' => array(
                'name' => 'A',
                'children' => array(
                    'codeAA' => array(
                        'name' => 'A-A',
                    ),
                ),
            ),
            'codeB' => array(
                'name' => 'B',
            ),
        );
    }

    protected function mockTree2ToFlat()
    {
        return array(
            'codeA' => array(
                'name' => 'A',
                'parent_id' => '',
                'id' => 'codeA',
            ),
            'codeAA' => array(
                'name' => 'A-A',
                'parent_id' => 'codeA',
                'id' => 'codeAA',
            ),
            'codeB' => array(
                'name' => 'B',
                'parent_id' => '',
                'id' => 'codeB',
            ),
        );
    }
}
