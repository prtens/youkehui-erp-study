<?php

namespace AppBundle\Common;

use Overtrue\Pinyin\Pinyin;

class PinyinToolkit
{
    protected static $pinyin;

    protected static function getPinyin()
    {
        if (!self::$pinyin) {
            self::$pinyin = new Pinyin();
        }

        return self::$pinyin;
    }

    public static function convert($string, $glue = '')
    {
        $pinyin = self::getPinyin();

        return implode($glue, $pinyin->convert($string, PINYIN_NONE));
    }
}
