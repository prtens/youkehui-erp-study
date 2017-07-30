<?php

namespace AppBundle\Extension;

use Codeages\Biz\Framework\Context\BizAware;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use AppBundle\Extension\DataTag\CategoriesDataTag;
use AppBundle\Extension\DataTag\CategoryChoicesDataTag;
use AppBundle\Extension\DataTag\RegionsDataTag;
use AppBundle\Extension\DataTag\RegionChoicesDataTag;
use AppBundle\Extension\DataTag\RoleChoicesDataTag;

class DataTagExtension extends BizAware implements ExtensionInterface, ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['extension.data_tag.categories'] = function ($biz) {
            $instance = new CategoriesDataTag();
            $instance->setBiz($biz);

            return $instance;
        };

        $container['extension.data_tag.category_choices'] = function ($biz) {
            $instance = new CategoryChoicesDataTag();
            $instance->setBiz($biz);

            return $instance;
        };

        $container['extension.data_tag.regions'] = function ($biz) {
            $instance = new RegionsDataTag();
            $instance->setBiz($biz);

            return $instance;
        };

        $container['extension.data_tag.region_choices'] = function ($biz) {
            $instance = new RegionChoicesDataTag();
            $instance->setBiz($biz);

            return $instance;
        };

        $container['extension.data_tag.role_choices'] = function ($biz) {
            $instance = new RoleChoicesDataTag();
            $instance->setBiz($biz);

            return $instance;
        };
    }

    public function payload(array $arguments)
    {
        $dataTagName = 'extension.data_tag.'.$arguments['data_tag_name'];
        unset($arguments['data_tag_name']);

        return $this->biz[$dataTagName]->getData($arguments);
    }

    public function getName()
    {
        return 'data_tag';
    }
}
