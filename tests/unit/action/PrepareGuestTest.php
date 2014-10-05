<?php

namespace sleepwalker\hoauth\tests\unit\action;

class PrepareGuestTest extends \sleepwalker\hoauth\tests\HTestCase
{
    public $model;
    public $action;
    public $oauth;

    public function setUp()
    {
        $this->model = $this->getMockBuilder('User')
            ->setMockClassName('TestUser')
            ->disableOriginalConstructor()
            ->setMethods(array('findByEmail', 'query'))
            ->getMock()
            ;
        $this->model->method('query')->willReturn($this->model);
        $this->model->method('findByEmail')->willReturn($this->model);

        $this->action = $this->getMockBuilder('\sleepwalker\hoauth\HOAuthAction')
            ->disableOriginalConstructor()
            ->setMethods(array('getUserModel', 'processUser'))
            ->getMock()
            ;
        $this->action
            ->method('getUserModel')
            ->willReturn($this->model);
        $this->action->model = get_class($this->model);

        $this->oauth = $this->getMock('\sleepwalker\hoauth\models\UserOAuth', array('getIsBond', 'getProfile'));
        $this->action->setOauth($this->oauth);

        \sleepwalker\hoauth\HOAuthAction::$useYiiUser = false;
    }

    public function testPrepareGuestAlreadyRegistered()
    {
        $this->action
            ->expects($this->never())
            ->method('processUser');

        $this->oauth
            ->method('getIsBond')
            ->will($this->returnValue(true));


        $result = $this->inv($this->action, 'prepareGuestUser');

        $this->assertEquals(
            array($this->model, false), $result,
            "prepareGuestUser should return current model and false for \$isNewUser"
        );
    }

    public function testPrepareGuestExistedUserNotBondAlwaysCheckPass()
    {
        $this->action
            ->expects($this->once())
            ->method('processUser')
            ->willReturn($this->model);

        $this->oauth
            ->method('getIsBond')
            ->will($this->returnValue(false));
        $this->oauth
            ->method('getProfile')
            ->will($this->returnValue((object)array(
                'emailVerified' => 'test@test.ru',
                )));

        $result = $this->inv($this->action, 'prepareGuestUser');

        $this->assertEquals(
            array($this->model, false), $result,
            "prepareGuestUser should return current model and false for \$isNewUser and should not check password of registered user"
            );
    }

    public function testPrepareGuestExistedUserNotBond()
    {
        $this->action
            ->expects($this->never())
            ->method('processUser')
            ->willReturn($this->model);
        $this->action->alwaysCheckPass = false;

        $this->oauth
            ->method('getIsBond')
            ->will($this->returnValue(false));
        $this->oauth
            ->method('getProfile')
            ->will($this->returnValue((object)array(
                'emailVerified' => 'test@test.ru',
                )));


        $result = $this->inv($this->action, 'prepareGuestUser');

        $this->assertEquals(
            array($this->model, false), $result,
            "prepareGuestUser should return current model and false for \$isNewUser and should not check password of registered user"
            );
    }

    public function testPrepareGuestNewUser()
    {
        $this->action
            ->expects($this->once())
            ->method('processUser')
            ->will($this->returnArgument(0));

        $result = $this->inv($this->action, 'prepareGuestUser');

        $this->assertTrue($result[1],
            "prepareGuestUser should return true, because it creates new user"
            );
        $this->assertTrue($result[0]->isNewRecord,
            "prepareGuestUser should create new user model, which has isNewRecord == true"
            );
        $this->assertEquals(
            get_class($this->model), get_class($result[0]),
            "prepareGuestUser should create new user model and return it"
            );
    }
}
