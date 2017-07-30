<?php

namespace Biz;

use Mockery;
use Biz\User\CurrentUser;

abstract class BaseTestCase extends \Codeages\Biz\Framework\UnitTests\BaseTestCase
{
    protected function createService($alias)
    {
        return $this->getBiz()->service($alias);
    }

    protected function createDao($alias)
    {
        return $this->getBiz()->Dao($alias);
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $_SERVER['HTTP_HOST'] = 'test.com'; //mock $_SERVER['HTTP_HOST'] for http request testing
    }

    public function setUp()
    {
        $biz = $this->getBiz();
        parent::emptyDatabaseQuickly();
        if (isset($biz['redis'])) {
            $biz['redis']->flushDb();
        }
        $this->initCurrentUser();
    }

    public function tearDown()
    {
        $biz = $this->getBiz();
        $keys = $biz->keys();

        foreach ($keys as $key) {
            if (substr($key, 0, 1) === '@') {
                unset($biz[$key]);
            }
        }
    }

    protected function initCurrentUser()
    {
        $biz = $this->getBiz();

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 1,
            'nickname' => 'admin',
            'login_ip' => '127.0.0.1',
            'roles' => array('ROLE_SUPER_ADMIN'),
            'region_id' => '0',
            'parent_id' => 1,
        ));
        $biz['user'] = $currentUser;

        return $this;
    }

    /**
     * mock对象
     *
     * @param string $objectName mock的类名
     * @param array  $params     mock对象时的参数,array,包含 $functionName,$withParams,$runTimes和$returnValue
     */
    protected function mock($objectName, $params = array())
    {
        $newService = explode('.', $objectName);
        $mockObject = Mockery::mock($newService[1]);

        foreach ($params as $param) {
            $mockObject->shouldReceive($param['functionName'])->times($param['runTimes'])->withAnyArgs()->andReturn($param['returnValue']);
        }
    }

    protected function mockBiz($alias, $params = array())
    {
        $aliasList = explode(':', $alias);
        $className = end($aliasList);
        $mockObj = Mockery::mock($className);

        foreach ($params as $param) {
            $mockObj->shouldReceive($param['functionName'])->withAnyArgs()->andReturn($param['returnValue']);
        }

        $biz = $this->getBiz();
        $biz['@'.$alias] = $mockObj;
    }

    protected function assertArrayEquals(array $ary1, array $ary2, array $keyAry = array())
    {
        if (!empty($keyAry)) {
            foreach ($keyAry as $key) {
                $this->assertEquals($ary1[$key], $ary2[$key]);
            }
        } else {
            foreach (array_keys($ary1) as $key) {
                $this->assertEquals($ary1[$key], $ary2[$key]);
            }
        }
    }

    /**
     * @return \Codeages\Biz\Framework\Context\Biz
     */
    protected function getBiz()
    {
        return self::$biz;
    }

    /**
     * @return \Biz\User\CurrentUser
     */
    protected function getCurrentUser()
    {
        return self::$biz['user'];
    }
}
