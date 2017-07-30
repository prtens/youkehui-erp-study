<?php

namespace AppBundle\Twig;

class PermissionExtension extends \Twig_Extension
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('permissions', array($this, 'findPermissionsByType')),
        );
    }

    public function findPermissionsByType($type)
    {
        $permissionProvider = $this->getPermissionProvider();

        return $permissionProvider->findPermissionsByType($type);
    }

    /**
     * @return \Biz\Permission\Provider\PermissionProvider
     */
    protected function getPermissionProvider()
    {
        $biz = $this->container->get('biz');

        return $biz['permission_provider'];
    }

    public function getName()
    {
        return 'app_twig_extension_permission';
    }
}
