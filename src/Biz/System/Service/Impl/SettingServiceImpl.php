<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\SettingService;

class SettingServiceImpl extends BaseService implements SettingService
{
    private $cached;

    public function set($name, $value)
    {
        $currentUser = $this->getCurrentUser();
        $this->getSettingDao()->deleteByNameAndOwnerId($name, $currentUser->getParentId());
        $setting = array(
            'name' => $name,
            'value' => $value,
            'owner_id' => $currentUser->getParentId(),
        );
        $this->getSettingDao()->create($setting);
        $this->clearCache();
    }

    public function get($name, $default = array())
    {
        if (empty($this->cached)) {
            $settings = $this->getSettingDao()->findByOwnerId($this->getCurrentUser()->getParentId());
            foreach ($settings as $setting) {
                $this->cached[$setting['name']] = $setting['value'];
            }
        }

        return $this->cached[$name];
    }

    protected function clearCache()
    {
        $this->cached = null;
    }

    /**
     * @return \Biz\System\Dao\SettingDao
     */
    protected function getSettingDao()
    {
        return $this->createDao('System:SettingDao');
    }
}
