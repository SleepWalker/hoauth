<?php
namespace sleepwalker\hoauth\tests\unit\action;

class HOAuthActionTest extends \sleepwalker\hoauth\tests\HTestCase
{
    public $model;
    public $action;

    public function setUp()
    {
        $this->model = $this->getMockBuilder('\User')
        ->setMockClassName('TestUser')
        ->disableOriginalConstructor()
        ->setMethods(array('findByEmail', 'query'))
        ->getMock()
        ;
        $this->model->method('query')->willReturn($this->model);
        $this->model->method('findByEmail')->willReturn($this->model);

        \sleepwalker\hoauth\HOAuthAction::$useYiiUser = false;

        $controller = $this->getMock('\Controller', array(), array('default'));
        $this->action = new \sleepwalker\hoauth\HOAuthAction($controller, 'hoauth');
        $this->action->attributes = array(
            'email' => 'email',
        );
        $this->action->model = get_class($this->model);
    }

    public function testNoModel()
    {
        $this->action->model = '';
        $exceptionThrown = false;

        try {
            $this->inv($this->action, 'setUp');
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue(
            $exceptionThrown,
            'Action should throw exception, when non existed model class provided'
        );
    }

    public function testNoEmail()
    {
        $this->action->attributes = array();
        $exceptionThrown = false;
        try {
            $this->inv($this->action, 'setUp');
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue(
            $exceptionThrown,
            'Action should throw exception, when the email field was not bound to the model\'s attributes'
        );
    }

    public function testNoFindByEmail()
    {
        $stub = $this->getMock('CActiveRecord');
        $this->action->model = get_class($stub);

        $exceptionThrown = false;
        try {
            $this->inv($this->action, 'setUp');
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue(
            $exceptionThrown,
            'Action should throw exception, when the model has no findByEmail() method'
        );
    }

    public function testEmailAttribute()
    {
        $emailAtt = 'userMailAtt';
        $this->action->attributes = array($emailAtt => 'email');
        $this->inv($this->action, 'setUp');

        $this->assertEquals(
            $this->action->emailAttribute, $emailAtt,
            'Action should properly set up email attrbute'
        );
    }

    public function testYiiUserEmail()
    {
        \sleepwalker\hoauth\HOAuthAction::$useYiiUser = true;
        $this->action->attributes['test'] = 'email';
        $this->inv($this->action, 'setUp');

        $this->assertEquals(
            $this->action->emailAttribute, 'email',
            "The email attribute should always be set to `email`"
        );
    }

    public function testYiiUserUsername()
    {
        \sleepwalker\hoauth\HOAuthAction::$useYiiUser = true;
        $this->inv($this->action, 'setUp');

        $this->assertEquals(
            $this->action->usernameAttribute, 'username',
            "The username attribute should always be set to `username`"
        );
    }

    public function testAttributesNotArray()
    {
        \sleepwalker\hoauth\HOAuthAction::$useYiiUser = true;
        $this->action->attributes = 'Hello World!';

        $exceptionThrown = false;
        try {
            $this->inv($this->action, 'setUp');
        } catch (Exception $e) {
            $exceptionThrown = true;
        }

        $this->assertFalse(
            $exceptionThrown,
            'There should be no exception if the attributes not array'
        );

        $this->assertTrue(
            is_array($this->action->attributes),
            'The method should reset `attributes` to array'
        );
    }

    public function testYiiUserStatus()
    {
        \sleepwalker\hoauth\HOAuthAction::$useYiiUser = true;
        $this->inv($this->action, 'setUp');

        $this->assertArrayHasKey(
            'status', $this->action->attributes,
            "The setup method should set the default user status for yii-user"
        );
    }

    public function testUseYiiUserAutoInitiation()
    {
        // test for covering all the ::setUp() lines
        \sleepwalker\hoauth\HOAuthAction::$useYiiUser = null;
        $this->inv($this->action, 'setUp');

        $this->assertNotNull(
            \sleepwalker\hoauth\HOAuthAction::$useYiiUser,
            'The setUp method should set the useYiiUser variable if it is not setted'
        );
    }

    /**
     * User model getter
     */
    public function testGetUserModelTest()
    {
        // TODO: проблема с тем, что мы не можем подменить класс, только инстансы
        // TODO: попробовать переопределить статический метод

        // $this->model
        //   ->method('query')
        //   ->will($this->returnValue($this->model));

        // $this->assertEquals(
        //   get_class($this->model), get_class($this->action->userModel),
        //   "The method should return the ".get_class($this->model)." model"
        //   );
    }
    public function testGetYiiUserModelTest()
    {
        // // test for covering all the ::setUp() lines
        // \sleepwalker\hoauth\HOAuthAction::$useYiiUser = true;
        // $this->assertEquals(
        //   'User', get_class($this->action->userModel),
        //   "The method should return User model of yii-user"
        //   );
    }

    /**
     * =====================================
     *             AUTHORIZATION
     * =====================================
     */
    public function testGetSetOauth()
    {
        $o = new \sleepwalker\hoauth\models\UserOAuth();
        $this->action->setOauth($o);

        $this->assertEquals(
            $o, $this->action->getOauth(),
            "Action should correctly handle getters and setters of oauth model"
        );
    }

    public function testAccessCodeSignedIn()
    {
        $oAuth = $this->getMock('\sleepwalker\hoauth\models\UserOAuth', array('bindTo'));

        $oAuth->expects($this->once())
            ->method('bindTo');

        $this->action->setOauth($oAuth);

        \Yii::app()->user->setState('__id', 1);// user logged in

        $return = $this->inv($this->action, 'getAccessCode');

        $this->assertEquals(
            1, $return,
            "Action should bind social network account without any questions, when the user logged in"
        );
    }
}

// TODO: populate Model callback
// TODO: колбек для возможности переопределить способ сохранения юзера
// TODO: возможность задать другую модель для профиля юзера или колбек, что бы повлиять на это

// TODO: throw exception when bond but user not exists

/*
if($this->callback("NAME", ...)) {
$this->internalFunc();
$this->afterCallback();
}
callback:
- fires event
- fires method on controller
- based on returned value returns false if:
- there was an error in callback
- or callback wants preventDefault() in this case the `after` should be called
 */
