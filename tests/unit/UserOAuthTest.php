<?php
Yii::import('ext.hoauth.models.*');
Yii::import('ext.hoauth.*');
ob_start();
class UserOAuthTest extends CDbTestCase
{
  public $fixtures=array(
    'cusers' => 'ConventionalUser',
    'yiiusers' => 'User',
    'oauth' => 'UserOAuth',
  );

  /**
   * Simple coverage testing of all function to find stupid bugs
   */
  public function testCoverage()
  {
    // tests also getConfigPath()
    $this->assertTrue(is_array(UserOAuth::getConfig()));

    $oAuth = UserOAuth::model()->findUser(1);
    $this->assertTrue(is_array($oAuth) && count($oAuth) > 0);
    $oAuth = UserOAuth::model()->findUser(1, 'Facebook');
    $this->assertInstanceOf('UserOAuth', $oAuth);

    $hybridAuth = $oAuth->hybridAuth;

    // TODO: authenticate

    // tests also getAdapter()
    // $cachedProfile = $oAuth->profile;  

    $oAuth->logout();

    $this->assertInstanceOf('stdClass', $oAuth->profileCache);

    $this->assertTrue($oAuth->bindTo(99));
    $this->assertTrue($oAuth->isBond);

    // after saving of model (in method bindTo()) profile 
    // should be also avaible and not in serialized form
    $this->assertInstanceOf('stdClass', $oAuth->profileCache);

    // and the last test is recreating of table for extension
    $createDbTable = self::getMethod(get_class($oAuth), 'createDbTable');
    $createDbTable->invokeArgs($oAuth, array());
  }

  /**
   * helper method to get protected methods for unit testing
   */
  protected static function getMethod($class, $methodName) 
  {
    $class = new ReflectionClass($class);
    $method = $class->getMethod($methodName);
    $method->setAccessible(true);
    return $method;
  }

  /**
   * Просто выводим буффер (баг с отправкой заголовков до того, как начнется сессия)
   */
  public function testEnd()
  {
    echo ob_end_clean();
  }
}
