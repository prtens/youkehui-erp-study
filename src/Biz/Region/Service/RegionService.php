<?php

namespace Biz\Region\Service;

interface RegionService
{
    public function getRegion($id);

    public function findRegions();

    public function findRegionsByParentId($parentId);

    public function findRegionsByIds(array $ids);

    public function createRegion(array $region);

    public function updateRegion($id, array $fields);

    public function deleteRegion($id);
}
