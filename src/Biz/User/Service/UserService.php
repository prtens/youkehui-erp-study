<?php

namespace Biz\User\Service;

interface UserService
{
    public function getUser($id);

    public function getUserByLoginField($keyword);

    public function register($registration);

    public function updateUser($id, $updateUser);

    public function deleteUser($id);

    public function isNicknameAvailable($nickname);

    public function isSubuserNicknameAvailable($nickname);

    public function isEmailAvailable($email);

    public function isMobileAvailable($mobile);

    public function searchUsers($conditions, $orderBy, $start, $limit);

    public function countUsers($conditions);

    public function findUsersByIds(array $ids);

    public function enableUser($id);

    public function disableUser($id);

    public function changePassword($id, $password);

    public function markLoginInfo();

    public function canDeleteUser($id);
}
