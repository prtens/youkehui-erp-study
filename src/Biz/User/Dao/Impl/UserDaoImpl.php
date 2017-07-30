<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserDaoImpl extends GeneralDaoImpl implements UserDao
{
    protected $table = 'user';

    public function getByEmail($email)
    {
        return $this->getByFields(array('email' => $email));
    }

    public function getByMobile($mobile)
    {
        return $this->getByFields(array('mobile' => $mobile));
    }

    public function getByNickname($nickname)
    {
        return $this->getByFields(array('nickname' => $nickname));
    }

    public function getByNicknameAndParentId($nickname, $parentId)
    {
        return $this->getByFields(array('nickname' => $nickname, 'parent_id' => $parentId));
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return array(
            'timestamps' => array(
                'created_time',
                'updated_time',
            ),
            'orderbys' => array(
                'id',
                'updated_time',
                'created_time',
            ),
            'serializes' => array(
                'roles' => 'delimiter',
            ),
            'conditions' => array(
                'id = :id',
                'nickname like :nickname',
                'nickname = :nickname_eq',
                'roles like :roles_like',
                'mobile = :mobile',
                'type = :type',
                'locked = :locked',
                'region_id = :region_id',
                'region_id in (:region_ids)',
                'parent_id = :parent_id',
            ),
        );
    }
}
