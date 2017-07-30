<?php

namespace AppBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use AppBundle\Common\ArrayToolkit;

class PermissionListener
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $biz = $this->container->get('biz');
        $currentUser = $biz['user'];

        //未登录的权限判断交给access_control控制
        if (!$currentUser->isLogin()) {
            return;
        }

        if ($currentUser->isSuperAdmin() || $currentUser->isAdmin()) {
            return;
        }

        // $route = $this->container->get('router')->getMatcher()->match($request->getPathInfo());

        if ($currentUser->hasPermission($request->getPathInfo())) {
            return;
        }

        // 没有录入权限菜单的先放行
        $enabledPermissions = $this->getPermissionProvider()->findPermissionsByType('left_menu');
        $enabledPermissions = ArrayToolkit::treeToFlat($enabledPermissions);
        $enabledPermissions = ArrayToolkit::column($enabledPermissions, 'path');
        if (!in_array($request->getPathInfo(), $enabledPermissions)) {
            return;
        }

        $self = $this;
        $event->setController(function () use ($self, $request) {
            return $self->container->get('templating')->renderResponse('AppBundle:role:access-denied.html.twig');
        });
    }

    /**
     * @return \Biz\Permission\Provider\PermissionProvider
     */
    protected function getPermissionProvider()
    {
        $biz = $this->container->get('biz');

        return $biz['permission_provider'];
    }
}
