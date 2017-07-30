<?php

namespace Tests\User\Service;

use Biz\BaseTestCase;

class UserServiceTest extends BaseTestCase
{
    public function testRegisterUser()
    {
        $createdUsers = $this->createUsers();
        $createdUser = $createdUsers[0];

        $users = $this->mockUsers();
        $user = $users[0];

        unset($user['password']);

        $this->assertArrayEquals($user, $createdUser, array_keys($user));
    }

    public function testIsNicknameAvailable()
    {
        $createdUsers = $this->createUsers();
        $createdUser = $createdUsers[0];

        $this->assertEquals(false, $this->getUserService()->isNicknameAvailable($createdUser['nickname']));
    }

    public function testIsEmailAvailable()
    {
        $createdUsers = $this->createUsers();
        $createdUser = $createdUsers[0];

        $this->assertEquals(false, $this->getUserService()->isEmailAvailable($createdUser['email']));
    }

    public function testIsMobileAvailable()
    {
        $createdUsers = $this->createUsers();
        $createdUser = $createdUsers[0];

        $this->assertEquals(false, $this->getUserService()->isMobileAvailable($createdUser['mobile']));
    }

    protected function createUsers()
    {
        $users = $this->mockUsers();

        $createdUsers = array();

        foreach ($users as $key => $user) {
            $createdUsers[$key] = $this->getUserService()->register($user);
        }

        return $createdUsers;
    }

    public function testUpdateUser()
    {
        $createdUsers = $this->createUsers();
        $createdUser = $createdUsers[0];

        $fields =
            array('nickname' => '张三', 'type' => 'alias', 'email' => 'xx111@zlzkj.com', 'mobile' => 18605817736, 'password' => '123456', 'company_name' => '张三加盟公司', 'created_ip' => '127.0.0.1');

        $updateUser = $this->getUserService()->updateUser($createdUser['id'], $fields);

        $this->assertEquals($fields['email'], $updateUser['email']);
    }

    public function testDeleteUser()
    {
        $createdUsers = $this->createUsers();
        $createdUser = $createdUsers[0];

        $this->getUserService()->deleteUser($createdUser['id']);

        $deleteUser = $this->getUserService()->getUser($createdUser['id']);

        $this->assertEquals(null, $deleteUser);
    }

    protected function mockUsers()
    {
        $users = array(
            array('nickname' => '张三', 'type' => 'alias', 'email' => 'xx1@zlzkj.com', 'mobile' => 18605817736, 'password' => '123456', 'company_name' => '张三加盟公司', 'created_ip' => '127.0.0.1'),
            array('nickname' => '李四', 'type' => 'alias', 'email' => 'xx2@zlzkj.com', 'mobile' => 18605817737, 'password' => '123456', 'company_name' => '李四加盟公司', 'created_ip' => '127.0.0.1'),
            array('nickname' => '王五', 'type' => 'alias', 'email' => 'xx3@zlzkj.com', 'mobile' => 18605817738, 'password' => '123456', 'company_name' => '王五加盟公司', 'created_ip' => '127.0.0.1'),
            array('nickname' => '赵七', 'type' => 'provider', 'email' => 'xx4@zlzkj.com', 'mobile' => 18605817739, 'password' => '123456', 'company_name' => '赵七加盟公司', 'created_ip' => '127.0.0.1'),
            array('nickname' => 'admin', 'type' => '', 'email' => 'xx4@zlzkj.com', 'mobile' => 18605817740, 'password' => 'ceshi', 'company_name' => '', 'created_ip' => '127.0.0.1'),
        );

        return $users;
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
