<?php

/**
 * Simple DummyUserIdentity class to handle authentication in `yii-user` module
 *
 * @version 1.2.4
 * @copyright Copyright &copy; 2013 Sviatoslav Danylenko
 * @author Sviatoslav Danylenko <dev@udf.su> 
 * @license MIT ({@link http://opensource.org/licenses/MIT})
 * @link https://github.com/SleepWalker/hoauth
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
