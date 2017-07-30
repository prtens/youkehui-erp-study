<?php

namespace Biz\User\Event;

use Codeages\Biz\Framework\Context\BizAware;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEventSubscriber extends BizAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'user.created' => 'onUserCreated',
        );
    }

    public function onUserCreated(Event $event)
    {
        $user = $event->getSubject();

        if ($user['type'] == 'alias') {
            $this->createDefaultAliasRoles($user);
            $this->createDefaultAliasSalesman($user);
        }
    }

    protected function createDefaultAliasRoles($alias)
    {
        $roles = array(
            array('name' => '经理', 'code' => 'ROLE_MANAGER', 'access_rules' => array(), 'owner_id' => $alias['id'], 'is_system' => 1),
            array('name' => '销售员', 'code' => 'ROLE_SALESMAN', 'access_rules' => array(), 'owner_id' => $alias['id'], 'is_system' => 1),
        );

        foreach ($roles as $role) {
            $this->getRoleService()->createRole($role);
        }
    }

    protected function createDefaultAliasSalesman($alias)
    {
        $salesman = array();
        $salesman['nickname'] = '默认销售员';
        $salesman['type'] = 'alias-subuser';
        $salesman['mobile'] = '';
        $salesman['email'] = '';
        $salesman['region_id'] = !empty($alias['region_id']) ? $alias['region_id'] : 0;
        $salesman['parent_id'] = $alias['id'];
        $salesman['company_name'] = !empty($alias['company_name']) ? $alias['company_name'] : $alias['nickname'];
        $salesman['company_address'] = !empty($alias['company_address']) ? $alias['company_address'] : '';
        $salesman['created_ip'] = !empty($alias['created_ip']) ? $alias['created_ip'] : '';
        $salesman['salt'] = $alias['salt'];
        $salesman['password'] = $alias['password'];
        $salesman['roles'] = array('ROLE_SALESMAN');

        $this->getUserService()->register($salesman);
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return \Biz\Permission\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->biz->service('Permission:RoleService');
    }
}
