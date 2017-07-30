<?php

namespace AppBundle\Extension\DataTag;

class RegionsDataTag extends BaseDataTag
{
    /**
     * 获取所有区域.
     */
    public function getData(array $arguments)
    {
        return $this->getRegionService()->findRegions();
    }

    /**
     * @return \Biz\Region\Service\RegionService
     */
    protected function getRegionService()
    {
        return $this->createService('Region:RegionService');
    }
}
