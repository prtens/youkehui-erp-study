<?php

namespace Biz\Permission\Provider;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use AppBundle\Common\ArrayToolkit;

class PermissionProvider
{
    private $biz;

    private $cache = array();

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function findPermissionsByType($type)
    {
        if ($this->isDebug() === false && $this->getCache($type) !== false) {
            return $this->getCache($type);
        }

        $allPermissions = $this->findAllOriginPermissions();
        if (!isset($allPermissions[$type])) {
            throw new \RuntimeException(sprintf('menus type `%s` does not exist', $type));
        }

        $permissions = $this->fillDefaultValues($allPermissions[$type]);
        $this->setCache($type, $permissions);

        return $permissions;
    }

    protected function fillDefaultValues($permissions)
    {
        $allPermissions = $this->findAllOriginPermissions();
        if (!isset($allPermissions['default'])) {
            return $permissions;
        }

        $default = $allPermissions['default'];
        $flatPermissions = ArrayToolkit::treeToFlat($permissions);
        foreach ($flatPermissions as &$permission) {
            $permission = array_merge($default, $permission);
        }

        return ArrayToolkit::flatToTree($flatPermissions, '');
    }

    protected function findAllOriginPermissions()
    {
        $yamls = $this->findPermissionYamls();
        $permissions = array();
        foreach ($yamls as $yaml) {
            $menus = Yaml::parse(file_get_contents($yaml));
            $permissions = array_merge($permissions, $menus);
        }

        return $permissions;
    }

    protected function findPermissionYamls()
    {
        $yamls = array();

        $rootDir = $this->biz['root_directory'];

        $finder = new Finder();
        $finder->files()->in($rootDir.'/src/*Bundle/Resources/')->name('menus.yml');

        foreach ($finder as $dir) {
            $filepath = $dir->getRealPath();
            if (file_exists($filepath)) {
                $yamls[] = $filepath;
            }
        }

        return $yamls;
    }

    protected function getCache($key)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        return false;
    }

    protected function setCache($key, $value)
    {
        $this->cache[$key] = $value;
    }

    protected function isDebug()
    {
        return $this->biz['debug'];
    }
}
