<?php

namespace Biz\System\Service;

interface SettingService
{
    public function set($name, $value);

    public function get($name, $default = null);
}
