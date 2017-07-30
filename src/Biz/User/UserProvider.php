<?php

namespace Biz\User;

use Biz\User\Service\UserService;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProvider implements UserProviderInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->getUserService()->getUserByLoginField($username);

        if (empty($user)) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $currentUser->setPermissions($this->getPermissionService()->findPermissionsByRoleCodes($currentUser->getRoles()));
        $biz = $this->container->get('biz');
        $biz['user'] = $currentUser;

        return $currentUser;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof CurrentUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Biz\User\CurrentUser';
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
