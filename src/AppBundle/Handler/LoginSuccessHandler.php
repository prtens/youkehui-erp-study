<?php

namespace AppBundle\Handler;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom login listener.
 */
class LoginSuccessHandler
{
    public function __construct(ContainerInterface $container, AuthorizationChecker $checker)
    {
        $this->container = $container;
        $this->checker = $checker;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($this->checker->isGranted('IS_AUTHENTICATED_FULLY')) {
            // user has just logged in
        }

        if ($this->checker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // user has logged in using remember_me cookie
        }

        // do some other magic here
        // $user = $event->getAuthenticationToken()->getUser();
        // $user->setPermissions($this->getPermissionService()->findPermissionsByRoleCodes($user->getRoles()));

        // $request = $event->getRequest();
        // $request->getSession()->set('login_ip', $request->getClientIp());

        $this->getUserService()->markLoginInfo();
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->container->get('biz')->service('User:UserService');
    }

    /**
     * @return \Biz\Permission\Service\PermissionService
     */
    protected function getPermissionService()
    {
        return $this->container->get('biz')->service('Permission:PermissionService');
    }
}
