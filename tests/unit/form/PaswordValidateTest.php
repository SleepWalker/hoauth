<?php

Yii::import('ext.hoauth.models.*');
Yii::import('ext.hoauth.*');
class PaswordValidateTest extends CTestCase
{
    public $form;
    public $user;

    public function setUp()
    {
        $this->user = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(array('findByEmail', 'query', 'verifyPassword'))
            ->getMock()
            ;
        $this->user->method('query')->willReturn($this->user);
        $this->user->method('findByEmail')->willReturn($this->user);

        $this->form = $this->getMockBuilder('HUserInfoForm')
                     ->setConstructorArgs(array($this->user, 'email', 'username'))
                     ->setMethods(null)
                     ->getMock()
                     ;
        $this->form->scenario = 'both_pass';
        $this->form->attributes = array(
            'username' => 'test',
            'email' => 'test@test.ru'
            );

        HOAuthAction::$useYiiUser = false;
    }

    public function testWrongPassword()
    {
        $this->form->password = 'test';
        $this->user->expects($this->once())
             ->method('verifyPassword');

        $this->validate();

        $this->assertHasErrors();
    }

    protected function validate()
    {
        $this->form->validatePassword('password', array());
    }

    protected function assertHasErrors()
    {
        $this->assertTrue($this->form->hasErrors('password'));
    }

    protected function assertNoErrors()
    {
        $this->assertFalse($this->form->hasErrors('password'));
    }
}

class User extends CActiveRecord
{
    public $email;
    public $username;
}