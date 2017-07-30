<?php

namespace AppBundle\Common\Tests;

use Biz\BaseTestCase;
use AppBundle\Common\PinyinToolkit;

class PinyinToolkitTest extends BaseTestCase
{
    public function testConvert()
    {
        $str = '椰奶果冻';
        $pinyin = 'yenaiguodong';

        $this->assertEquals($pinyin, PinyinToolkit::convert($str));
    }
}
