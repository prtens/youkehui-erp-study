<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\SimpleValidator;
use Biz\Common\Exception\NotFoundException;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Biz\Common\Exception\InvalidArgumentException;
use Biz\Common\Exception\AccessDeniedException;

class UserServiceImpl extends BaseService implements UserService
{
    public function getUser($id)
    {
        return $this->filterUserFields($this->getUserDao()->get($id));
    }

    public function getUserByLoginField($keyword)
    {
        // 子账号登录，只支持昵称
        if (strpos($keyword, ':') > 0) {
            list($nickname, $subNickname) = explode(':', $keyword);
            $user = $this->getUserDao()->getByNickname($nickname);
            $subUser = $this->getUserDao()->getByNicknameAndParentId($subNickname, $user['id']);

            return $subUser;
        }

        if (SimpleValidator::email($keyword)) {
            $user = $this->getUserDao()->getByEmail($keyword);
        } elseif (SimpleValidator::mobile($keyword)) {
            $user = $this->getUserDao()->getByMobile($keyword);
        } else {
            $user = $this->getUserDao()->getByNickname($keyword);
        }

        return $user;
    }

    public function register($registration)
    {
        $requiredfields = array('nickname', 'mobile', 'password');

        if (!ArrayToolkit::requires($registration, $requiredfields)) {
            throw new InvalidArgumentException('Missing required fields when register user');
        }

        $user = array();

        $user['nickname'] = $registration['nickname'];
        $user['type'] = !empty($registration['type']) ? $registration['type'] : 'alias';
        $user['mobile'] = $registration['mobile'];
        $user['email'] = !empty($registration['email']) ? $registration['email'] : '';
        $user['region_id'] = !empty($registration['region_id']) ? $registration['region_id'] : 0;
        $user['parent_id'] = !empty($registration['parent_id']) ? $registration['parent_id'] : 0;
        $user['company_name'] = !empty($registration['company_name']) ? $registration['company_name'] : $registration['nickname'];
        $user['company_address'] = !empty($registration['company_address']) ? $registration['company_address'] : '';
        $user['created_ip'] = !empty($registration['created_ip']) ? $registration['created_ip'] : '';
        $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $user['password'] = $this->getPasswordEncoder()->encodePassword($registration['password'], $user['salt']);

        if ($user['type'] == 'alias') {
            $user['roles'] = array('ROLE_ADMIN');
        } else {
            $user['roles'] = !empty($registration['roles']) ? $registration['roles'] : array('ROLE_USER');
        }

        //统一加上ROLE_USER
        if (!in_array('ROLE_USER', $user['roles'])) {
            array_push($user['roles'], 'ROLE_USER');
        }

        $user = $this->getUserDao()->create($user);

        $this->dispatchEvent('user.created', $user);

        $user = $this->filterUserFields($user);

        $this->getLogService()->info('user', 'create', "新增{$this->getUserType($user)}-{$user['nickname']}(#{$user['id']})", $user);

        return $user;
    }

    public function updateUser($id, $fields)
    {
        $originalUser = $this->getUser($id);

        if (!$this->isMobileAvailable($fields['mobile']) && $fields['mobile'] != $originalUser['mobile']) {
            throw new InvalidArgumentException(sprintf('Mobile already exist: %s', $fields['mobile']));
        }

        $user = array();

        $user['nickname'] = $fields['nickname'];
        if (!empty($fields['type'])) {
            $user['type'] = $fields['type'];
        }
        if (!empty($fields['roles'])) {
            $user['roles'] = $fields['roles'];
        }
        $user['mobile'] = $fields['mobile'];
        $user['email'] = !empty($fields['email']) ? $fields['email'] : '';
        $user['company_name'] = !empty($fields['company_name']) ? $fields['company_name'] : $fields['nickname'];
        $user['company_address'] = !empty($fields['company_address']) ? $fields['company_address'] : '';

        $user = $this->filterUserFields($this->getUserDao()->update($id, $user));

        $this->getLogService()->info('user', 'update', "更新{$this->getUserType($user)}-{$user['nickname']}(#{$user['id']})资料", $user);

        return $user;
    }

    public function deleteUser($id)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw new NotFoundException(sprintf('user id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($user);

        $this->getUserDao()->delete($id);

        $this->getLogService()->info('user', 'delete', "删除{$this->getUserType($user)}-{$user['nickname']}(#{$user['id']})");
    }

    public function isNicknameAvailable($nickname)
    {
        if (empty($nickname)) {
            return false;
        }

        $user = $this->getUserDao()->getByNickname($nickname);

        return empty($user) ? true : false;
    }

    public function isSubuserNicknameAvailable($nickname)
    {
        if (empty($nickname)) {
            return false;
        }

        $count = $this->countUsers(array(
            'nickname_eq' => $nickname,
            'type' => 'alias-subuser',
            'parent_id' => $this->getCurrentUser()->getId(),
        ));

        return $count == 0 ? true : false;
    }

    public function isEmailAvailable($email)
    {
        if (empty($email)) {
            return false;
        }

        $user = $this->getUserDao()->getByEmail($email);

        return empty($user) ? true : false;
    }

    public function isMobileAvailable($mobile)
    {
        if (empty($mobile)) {
            return false;
        }

        $user = $this->getUserDao()->getByMobile($mobile);

        return empty($user) ? true : false;
    }

    public function searchUsers($conditions, $orderBy, $start, $limit)
    {
        $users = $this->getUserDao()->search($conditions, $orderBy, $start, $limit);

        return $this->filterUsersFields($users);
    }

    public function countUsers($conditions)
    {
        return $this->getUserDao()->count($conditions);
    }

    public function findUsersByIds(array $ids)
    {
        $users = ArrayToolkit::index($this->getUserDao()->findByIds($ids), 'id');

        return $this->filterUsersFields($users);
    }

    public function enableUser($id)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw new NotFoundException(sprintf('user id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($user);

        $this->getUserDao()->update($id, array('locked' => 0));

        $this->getLogService()->info('user', 'enable', "启用账户-{$user['nickname']}(#{$user['id']})");
    }

    public function disableUser($id)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw new NotFoundException(sprintf('user id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($user);

        $this->getUserDao()->update($id, array('locked' => 1));

        $this->getLogService()->info('user', 'disable', "禁用账户-{$user['nickname']}(#{$user['id']})");
    }

    public function changePassword($id, $password)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw new NotFoundException(sprintf('user id#%s not found', $id));
        }

        $this->checkCurrentUserAccess($user);

        $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $password = $this->getPasswordEncoder()->encodePassword($password, $salt);

        $this->getUserDao()->update($id, array(
            'salt' => $salt,
            'password' => $password,
        ));

        $this->getLogService()->info('user', 'disable', "修改账户-{$user['nickname']}(#{$user['id']})密码");
    }

    public function markLoginInfo()
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            return;
        }

        $this->getUserDao()->update($user['id'], array(
            'login_ip' => $user['login_ip'],
            'login_time' => time(),
        ));

        if ($user['parent_id'] != 0) {
            $parentUser = $this->getUser($user['parent_id']);
            $this->getLogService()->info('user', 'login_success', "{$parentUser['nickname']}:{$user['nickname']}登录成功");
        } else {
            $this->getLogService()->info('user', 'login_success', "{$user['nickname']}登录成功");
        }
    }

    public function canDeleteUser($id)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw new NotFoundException(sprintf('user id#%s not found', $id));
        }

        //只限制供应商
        if ($user['type'] != 'provider') {
            return true;
        }

        $this->checkCurrentUserAccess($user);

        $count = $this->getGoodsService()->countGoods(array('provider_id' => $id));
        if ($count > 0) {
            return false;
        }

        return true;
    }

    protected function getUserType($user)
    {
        if ($user['type'] == 'alias-subuser') {
            return '子账户';
        } elseif ($user['type'] == 'alias') {
            return '加盟商';
        } else {
            return '供应商';
        }
    }

    protected function checkCurrentUserAccess($deleteUser)
    {
        $user = $this->getCurrentUser();

        if ($deleteUser['parent_id'] != $user->getParentId() && $deleteUser['id'] != $user->getParentId() && !$user->isSuperAdmin()) {
            throw new AccessDeniedException();
        }
    }

    protected function filterUsersFields($users)
    {
        foreach ($users as &$user) {
            $user = $this->filterUserFields($user);
        }

        return $users;
    }

    protected function filterUserFields($user)
    {
        unset($user['password']);
        unset($user['salt']);

        return $user;
    }

    protected function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }

    /**
     * @return \Biz\User\Dao\UserDao
     */
    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }

    /**
     * @return \Biz\System\Service\LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return \Biz\Goods\Service\GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }
}
