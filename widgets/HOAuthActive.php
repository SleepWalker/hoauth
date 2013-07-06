<?php
/**
 * HOAuthActive provides widget with buttons for login with social networs 
 * that ARE CONNECTED in HybridAuth 
 * 
 * @uses CWidget
 * @version 1.2.4
 * @copyright Copyright &copy; 2013 Charles Kefuver
 * @author Charles Kefauer <ckefauver@ibacom.es> 
 * @modified from HOAuth by Sviatoslav Danylenko <dev@udf.su> 
 * @license MIT ({@link http://opensource.org/licenses/MIT})
 * @link https://github.com/SleepWalker/hoauth
 */

/**
 * Displays social network login buttons for the user with specified id.
 * This widget is useful for the email activation page (the link, that 
 * you send in aktivation email). On this page you already know what user
 * had opened it, so you can display only that social button, that user had
 * used, when he was registered.
 *
 * Usage:
 * The usage of this widget is similar to the HOAuth widget, but you also need 
 * to pass current user model to it.
 *
 * NOTE: If you want to change the order of button it is better to change this 
 * order in HybridAuth config.php file
 */
require_once(dirname(__FILE__).'/HOAuth.php');
class HOAuthActive extends HOAuth
{
	public $user = null;

	public function run()
	{
		if (!$this->user)
			return;

		// find all authorizations from user with id=
		$userOAuths = UserOAuth::model()->findUser($this->user->primaryKey); 
		foreach($userOAuths as $userOAuth)
		{
			$this->render('link', array(
				'provider' => $userOAuth->provider,
				));
		}

	}
}
