<?php

/**
 * Simple DummyUserIdentity class to handle authentication in `yii-user` module
 */
class DummyUserIdentity extends CUserIdentity
{
  private $_id;

  public function __construct($id, $username)
  {
    $this->username = $username;
    $this->_id = $id;
    $this->errorCode=self::ERROR_NONE;
  }
  
	/**
	 * Authenticates a user.
   * @return boolean whether authentication succeeds.
   */
  public function authenticate()
  {
    return $this->errorCode==self::ERROR_NONE;
  }

  public function getId()
  {
    return $this->_id;
  }
}
