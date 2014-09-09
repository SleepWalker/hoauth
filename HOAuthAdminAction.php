<?php
/**
 * HOAuthAdminAction - administrative action. Helps to create config for HybridAuth
 * 
 * @uses CAction
 * @version 1.2.4
 * @copyright Copyright &copy; 2013 Sviatoslav Danylenko
 * @author Sviatoslav Danylenko <dev@udf.su> 
 * @license MIT ({@link http://opensource.org/licenses/MIT})
 * @link https://github.com/SleepWalker/hoauth
 */
class HOAuthAdminAction extends CAction
{
	/**
	 * @var string $endPointUrl url with wich will the social network comunicate
	 */
	private $endPointUrl;

	/**
	 * @var string $configPath path to the HybridAuth config file
	 */
	private $configPath; 

	/**
	 * @var string $route id of module and controller (eg. module/controller) for wich to generate oauth urls
	 */
	public $route = false;

	public function run()
	{
		if(!$this->route) {
			$this->route = $this->controller->module ? $this->controller->module->id . '/' . $this->controller->id : $this->controller->id . '/oauth';
		}

		$endpoint_url = Yii::app()->createAbsoluteUrl($this->route);
		$this->endPointUrl = $endpoint_url;

		require_once(dirname(__FILE__).'/models/UserOAuth.php');
		$this->configPath = UserOAuth::getConfigPath();

		$this->controller->renderText($this->getForm($this->configPath, $this->endPointUrl));
		Yii::app()->end();
	}

	public function getForm($config_path, $endpoint_url)
	{
		$path = dirname(__FILE__);

		ob_start();
		include($path.'/hybridauth/install.php');
		return ob_get_clean();
	}
}
