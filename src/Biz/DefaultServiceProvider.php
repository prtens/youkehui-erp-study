<?php

namespace Biz;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Biz\Permission\Provider\PermissionProvider;

class DefaultServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['permission_provider'] = function ($biz) {
            return new PermissionProvider($biz);
        };
    }
}
