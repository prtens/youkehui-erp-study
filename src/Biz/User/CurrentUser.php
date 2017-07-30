<?php

namespace Biz\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class CurrentUser implements AdvancedUserInterface, EquatableInterface, \ArrayAccess, \Serializable
{
    protected $data;

    protected $permissions;

    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        throw new \RuntimeException("{$name} is not exist in CurrentUser.");
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    public function clearNotifacationNum()
    {
        $this->data['new_notification_num'] = '0';
    }

    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->nickname;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getParentId()
    {
        return !empty($this->parent_id) ? $this->parent_id : $this->id;
    }

    public function eraseCredentials()
    {
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return !$this->locked;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return true;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function isEqualTo(UserInterface $user)
    {
        if ($this->email !== $user->getUsername()) {
            return false;
        }

        if (array_diff($this->roles, $user->getRoles())) {
            return false;
        }

        if (array_diff($user->getRoles(), $this->roles)) {
            return false;
        }

        return true;
    }

    public function isLogin()
    {
        return empty($this->id) ? false : true;
    }

    public function isSuperAdmin()
    {
        if (count(array_intersect($this->getRoles(), array('ROLE_SUPER_ADMIN'))) > 0) {
            return true;
        }

        return false;
    }

    public function isAdmin()
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (count(array_intersect($this->getRoles(), array('ROLE_ADMIN'))) > 0) {
            return true;
        }

        return false;
    }

    public function isManager()
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (count(array_intersect($this->getRoles(), array('ROLE_MANAGER'))) > 0) {
            return true;
        }

        return false;
    }

    public function isSalesman()
    {
        if ($this->isManager()) {
            return true;
        }

        if (count(array_intersect($this->getRoles(), array('ROLE_SALESMAN'))) > 0) {
            return true;
        }

        return false;
    }

    public function setPermissions($permissions)
    {
        return $this->permissions = $permissions;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function hasPermission($accessRule)
    {
        $currentPermissions = $this->getPermissions();

        if (in_array($accessRule, $currentPermissions)) {
            return true;
        }

        // todo url带id的情况

        return false;
    }

    public function fromArray(array $user)
    {
        $this->data = $user;

        return $this;
    }

    public function toArray()
    {
        return $this->data;
    }
}
