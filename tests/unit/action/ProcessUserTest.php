<?php
namespace sleepwalker\hoauth\tests\unit\action;

class ProcessUserTest extends \sleepwalker\hoauth\tests\HTestCase
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

    public function testX()
    {
        
    }
}
