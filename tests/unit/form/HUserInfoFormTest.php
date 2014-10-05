<?php
Yii::import('ext.hoauth.models.*');
Yii::import('ext.hoauth.*');
class HUserInfoFormTest extends CDbTestCase
{
	public $fixtures=array(
		'cusers' => 'ConventionalUser',
		'yiiusers' => 'User',
		'oauth' => 'UserOAuth',
	);

	/**
	 * Test HUserInfoForm class with use of non `yii-user` models
	 */
	public function testUserModel()
	{
		HOAuthAction::$useYiiUser = false;
		
		$user = new ConventionalUser();
		$this->runTestWith($user);
	}

	/**
	 * Test HUserInfoForm class with use of `yii-user` models
	 */
	public function testYiiUserModel()
	{
		HOAuthAction::$useYiiUser = true;
		
		Yii::import('application.modules.user.models.*');
		Yii::import('application.modules.user.components.*');
		
		$user = new User();
		$this->runTestWith($user);
	}
	
	/**
	 * testing auto scenario changing feature
	 */
	public function testScenarioChoosing()
	{
		HOAuthAction::$useYiiUser = false;
		$user = ConventionalUser::model()->findByPk(1);
		$form = new HUserInfoForm($user, 'email', 'username');
		
		$this->assertFalse($form->validateUser());
		// simulate form submiting
		$this->submitForm();
		$form->password = 'wrongpassword';
		$this->assertFalse($form->validateUser());
		$form->password = 'qwertyu';
		$this->assertTrue($form->validateUser());
		
		$user = new ConventionalUser();
		// test of scenario where username field is not required
		//****************************************************
		$user->username = $this->cusers['user1']['username'];
		$user->email = $this->cusers['user1']['email'];
		$form = new HUserInfoForm($user, 'email', false);
		$this->assertTrue($form->scenario == 'email');
		// simulate form submiting
		$this->submitForm();
		$this->assertFalse($form->validateUser());
		$this->assertTrue($form->scenario == 'email_pass');
		$form->password = 'qwertyu';
		$this->assertTrue($form->validateUser());
		
		$user = new User();
		HOAuthAction::$useYiiUser = true;
		// scenario when user had inserted email or username of existsing local user
		//****************************************************
		$user->unsetAttributes();
		$user->username = $this->yiiusers['user1']['username'];
		$user->email = $this->yiiusers['user1']['email'];
		$form = new HUserInfoForm($user, 'email', 'username');
		$this->assertTrue($form->scenario == 'both');
		// simulate form submiting
		$this->submitForm();
		$this->assertFalse($form->validateUser());
		$this->assertTrue($form->scenario == 'both_pass');
		//$form->password = 'qwertyu'; // confirming account with pass
		//$this->assertTrue($form->validateUser());
		//$form->password = null;
		$form->scenario = 'both'; // resetting scenario
		$form->email = 'fake@mail.ru'; // removing email of existing account
		$this->assertFalse($form->validateUser()); // but we still username of existing account
		$form->username = 'fake';
		$form->scenario = 'both'; // resetting scenario
		$this->assertTrue($form->validateUser());
		
		// new user
		//****************************************************
		$user->unsetAttributes();
		$user->username = 'vasia';
		$form = new HUserInfoForm($user, 'email', 'username');
		$this->assertFalse($form->validateUser()); // we still need to get username from user
		$form->scenario = 'both'; // resetting scenario
		$form->email = 'mymail@pupkin.org';
		$this->assertTrue($form->validateUser());
	}
	
	/**
	 * Universal test for both yii-user and user defined models
	 */
	protected function runTestWith($user)
	{
		$form = new HUserInfoForm($user, 'email', 'username');
		
		// simulate form submiting
		$this->submitForm();
		
		// scenario with non existing user
		$_POST['HUserInfoForm'] = array(
			'email' => 'noemail@hoauth.com',
			'username' => 'hoauthGuest',
		);
		$this->assertTrue($form->isFormValid);
		
		$form->scenario = 'both';
		$this->assertTrue($form->isFormValid);
		$this->assertTrue($form->validateUser());

		$_POST['HUserInfoForm'] = array(
			'email' => 'user1@mail.ru',
			'username' => 'user1',
		);

		$this->assertTrue($form->isFormValid);
		$this->assertFalse($form->validateUser());
		$this->assertTrue(count($form->getErrors('email')) > 0 && $form->scenario == 'both_pass');

		// checking password verifying features
		$this->assertFalse($form->isFormValid);
		$_POST['HUserInfoForm']['password'] = 'qwertyu';
		$this->assertTrue($form->validateUser());


		// if user provided correct password for existing account
		// the model property should change to that model, for wich user provided password
		// so:
		$this->assertNotEquals($form->model, $user);
		
		
		// getting valid model
		// only for code coverage
		$this->assertEquals($form->model, $form->validUserModel);
	}
	
	protected function submitForm()
	{
		// simulate form submiting
		$_POST['submit'] = $_POST['yform_huserinfoform'] = true;
	}

	// public function testResubmitExistingEmailWithAnotherExistingEmail()
	// {
	// 	$mock = $this->getMockBuilder('HUserInfoForm', array('validatePassword', 'getIsFormValid'))
	// 				 ->setConstructorArgs(array(new User, 'email', 'username'))
	// 				 ->getMock()
	// 				 ;
	// 	$mock->method('getIsFormValid')->willReturn(true);
		
	// 	$this->assertTrue($mock->validateUser());
	// }
}
