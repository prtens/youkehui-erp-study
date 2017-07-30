<?php

namespace Biz\Region\Service\Impl;

use Biz\BaseService;
use Biz\Region\Service\RegionService;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\Exception\NotFoundException;
use Biz\Common\Exception\InvalidArgumentException;

class RegionServiceImpl extends BaseService implements RegionService
{
    public function getRegion($id)
    {
        return $this->getRegionDao()->get($id);
    }

    public function findRegions()
    {
        return $this->getRegionDao()->findRegions();
    }

    public function findRegionsByParentId($parentId)
    {
        return $this->getRegionDao()->findByParentId($parentId);
    }

    public function findRegionsByIds(array $ids)
    {
        return ArrayToolkit::index($this->getRegionDao()->findByIds($ids), 'id');
    }

    public function createRegion(array $region)
    {
        $region = $this->filterCreateRegionFields($region);

        $region = $this->getRegionDao()->create($region);

        $this->getLogService()->info('region', 'create', "新增区域-{$region['name']}(#{$region['id']})", $region);

        return $region;
    }

    public function updateRegion($id, array $fields)
    {
        $region = $this->getRegion($id);

        if (empty($region)) {
            throw new NotFoundException(sprintf('region id#%s not found', $id));
        }

        $fields = $this->filterUpdateRegionFields($fields);

        $region = $this->getRegionDao()->update($id, $fields);

        $this->getLogService()->info('region', 'update', "更新区域-{$region['name']}(#{$region['id']})", $region);

        return $region;
    }

    public function deleteRegion($id)
    {
        $region = $this->getRegion($id);

        if (empty($region)) {
            throw new NotFoundException(sprintf('Region id#%s not found', $id));
        }

        $this->getRegionDao()->delete($id);

        $this->getLogService()->info('region', 'delete', "删除区域-{$region['name']}(#{$region['id']})");
    }

    protected function filterCreateRegionFields($fields)
    {
        if (!ArrayToolkit::requires($fields, array('name', 'parent_id', 'seq'))) {
            throw new InvalidArgumentException('Missing required fields when creating Region');
        }

        $fields = ArrayToolkit::parts($fields, array(
            'id',
            'name',
            'parent_id',
            'seq',
        ));

        if ($fields['parent_id'] == 0) {
            $fields['depth'] = 1;
        } else {
            $parentRegion = $this->getRegion($fields['parent_id']);
            if (empty($parentRegion)) {
                throw new NotFoundException(sprintf('region id#%s not found', $fields['parent_id']));
            }
            $fields['depth'] = (int) $parentRegion['depth'] + 1;
        }

        return array_merge($fields, $this->getDefaultRegionFields());
    }

    protected function filterUpdateRegionFields($fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'name',
            'seq',
        ));

        return $fields;
    }

    protected function getDefaultRegionFields()
    {
        return array(
            'seq' => 0,
        );
    }

    /**
     * @return \Biz\Region\Dao\RegionDao
     */
    protected function getRegionDao()
    {
        return $this->createDao('Region:RegionDao');
    }

    /**
     * @return \Biz\System\Service\LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
