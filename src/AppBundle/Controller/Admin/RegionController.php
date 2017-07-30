<?php

namespace AppBundle\Controller\Admin;

use Biz\Common\Exception\NotFoundException;
use Biz\Common\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class RegionController extends BaseController
{
    public function indexAction(Request $request)
    {
        $regions = $this->getRegionService()->findRegions();

        $regions = ArrayToolkit::flatToTree($regions, 0);

        return $this->render('AppBundle:admin/region:index.html.twig', array(
            'regions' => $regions,
        ));
    }

    public function createAction(Request $request)
    {
        $parentId = $request->query->get('parent_id', 0);

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $this->getRegionService()->createRegion($fields);

            return $this->createJsonResponse(true);
        }

        $defaultRegion = array(
            'id' => 0,
            'name' => '',
            'seq' => 0,
            'parent_id' => $parentId,
        );

        return $this->render('AppBundle:admin/region:modal.html.twig', array(
            'region' => $defaultRegion,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $region = $this->getRegionService()->getRegion($id);

        if (empty($region)) {
            throw new NotFoundException(sprintf('Region id#%s not found', $id));
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $this->getRegionService()->updateRegion($id, $fields);

            return $this->createJsonResponse(true);
        }

        return $this->render('AppBundle:admin/region:modal.html.twig', array(
            'region' => $region,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $region = $this->getRegionService()->getRegion($id);

        if (empty($region)) {
            throw new NotFoundException(sprintf('Region id#%s not found', $id));
        }

        if ($this->getUserService()->countUsers(array('region_id' => $id)) > 0) {
            return $this->createJsonResponse('无法删除，该区域正在使用中，请先将加盟商等人员移除该区域后重试');
        }

        $this->getRegionService()->deleteRegion($id);

        $children = $this->getRegionService()->findRegionsByParentId($region['id']);
        if (count($children) > 0) {
            throw new RuntimeException(sprintf('region id#%s delete failed, please remove its children first', $id));
        }

        return $this->createJsonResponse(true);
    }

    /**
     * @return \Biz\Region\Service\RegionService
     */
    protected function getRegionService()
    {
        return $this->createService('Region:RegionService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
