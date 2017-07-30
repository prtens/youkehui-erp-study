<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserDao extends GeneralDaoInterface
{
    public function getByEmail($email);

    public function getByMobile($mobile);

    public function getByNickname($nickname);

    public function getByNicknameAndParentId($nickname, $parentId);

    public function findByIds(array $ids);
}
