<?php

namespace AppBundle\Extension\DataTag;

use AppBundle\Common\ArrayToolkit;

class RegionChoicesDataTag extends BaseDataTag
{
    /**
     * 获取区域select控件数据.
     */
    public function getData(array $arguments)
    {
        $indent = isset($arguments['indent']) ? $arguments['indent'] : '　';

        $choices = array();

        $regions = $this->getRegionService()->findRegions();

        //排序
        $regions = ArrayToolkit::flatToTree($regions, 0);
        $regions = ArrayToolkit::treeToFlat($regions);

        foreach ($regions as $region) {
            $choices[$region['id']] = str_repeat($indent, ($region['depth'] - 1)).$region['name'];
        }

        return $choices;
    }

    /**
     * @return \Biz\Region\Service\RegionService
     */
    protected function getRegionService()
    {
        return $this->createService('Region:RegionService');
    }
}
