<?php

namespace tests\Region\Service;

use Biz\BaseTestCase;

class RegionServiceTest extends BaseTestCase
{
    public function testCreateRegion()
    {
        $createdRegions = $this->createRegions();
        $createdRegion = $createdRegions[0];

        $regions = $this->mockRegions();
        $region = $regions[0];

        $this->assertArrayEquals($region, $createdRegion, array_keys($region));
    }

    public function testGetRegion()
    {
        $createdRegions = $this->createRegions();
        $createdRegion = $createdRegions[0];

        $gettedRegion = $this->getRegionService()->getRegion($createdRegion['id']);
        $this->assertArrayEquals($createdRegion, $gettedRegion);
    }

    public function testUpdateRegion()
    {
        $createdRegions = $this->createRegions();
        $createdRegion = $createdRegions[0];

        $fields = array(
            'name' => '13',
            'parent_id' => '0',
        );

        $updateRegion = $this->getRegionService()->updateRegion($createdRegion['id'], $fields);

        $this->assertArrayEquals(array_merge($createdRegion, $fields), $updateRegion);
    }

    public function testDeleteRegion()
    {
        $createdRegions = $this->createRegions();
        $createdRegion = $createdRegions[0];

        $this->getRegionService()->deleteRegion($createdRegion['id']);

        $deleteRegion = $this->getRegionService()->getRegion($createdRegion['id']);

        $this->assertEquals(null, $deleteRegion);
    }

    /**
     * @expectedException \Biz\Common\Exception\NotFoundException
     */
    public function testDeleteNotExistRegion()
    {
        $this->getRegionService()->deleteRegion(-1);
    }

    protected function createRegions()
    {
        $regions = $this->mockRegions();

        $createdRegions = array();

        foreach ($regions as $key => $region) {
            $createdRegions[$key] = $this->getRegionService()->createRegion($region);
        }

        return $createdRegions;
    }

    protected function mockRegions()
    {
        $regions = array(
            array('id' => '1', 'parent_id' => '0', 'name' => '1', 'seq' => 0),
            array('id' => '2', 'parent_id' => '1', 'name' => '1-1', 'seq' => 2),
            array('id' => '3', 'parent_id' => '1', 'name' => '1-2', 'seq' => 3),
            array('id' => '4', 'parent_id' => '1', 'name' => '1-3', 'seq' => 4),
            array('id' => '5', 'parent_id' => '0', 'name' => '2', 'seq' => 0),
        );

        return $regions;
    }

    /**
     * @return \Biz\Region\Service\RegionService
     */
    protected function getRegionService()
    {
        return $this->createService('Region:RegionService');
    }
}
