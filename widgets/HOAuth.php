<?php
/**
 * HOAuth provides widget with buttons for login with social networs 
 * that enabled in HybridAuth config
 * 
 * @uses CWidget
 * @version 1.2.5
 * @copyright Copyright &copy; 2013 Sviatoslav Danylenko
 * @author Sviatoslav Danylenko <dev@udf.su> 
 * @license MIT ({@link http://opensource.org/licenses/MIT})
 * @link https://github.com/SleepWalker/hoauth
 */

/**
 * NOTE: If you want to change the order of button it is better to change this order in HybridAuth config.php file
 */
class HOAuth extends CWidget
{
	/**
	 * @var string $route id of module and controller (eg. module/controller) for wich to generate oauth urls
	 */
	public $route = false;

	/**
	 * @var boolean $onlyIcons the flag that displays social buttons as icons
	 */
	public $onlyIcons = false;

	/**
	 * @var integer $popupWidth the width of the popup window
	 */
	public $popupWidth = 480;

	/**
	 * @var integer $popupHeight the height of the popup window
	 */
	public $popupHeight = 680;

	public function init()
	{
		if(!$this->route)
			$this->route = $this->controller->module ? $this->controller->module->id . '/' . $this->controller->id : $this->controller->id;
		
		require_once(dirname(__FILE__).'/../models/UserOAuth.php');
		require_once(dirname(__FILE__).'/../HOAuthAction.php');
		$this->registerScripts();
	}

	public function run()
	{
		$config = UserOAuth::getConfig();
		echo CHtml::openTag('div', array(
			'id' => 'hoauthWidget' . $this->id,
			'class' => 'hoauthWidget',
			));

		foreach($config['providers'] as $provider => $settings)
			if($settings['enabled'])
				$this->render('link', array(
					'provider' => $provider,
				));

		echo CHtml::closeTag('div');
	}

	protected function registerScripts()
	{
		$assetsUrl = Yii::app()->getAssetManager()->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets',false,-1,YII_DEBUG);
    $cs = Yii::app()->getClientScript();
		$cs->registerCoreScript('jquery'); 
    $cs->registerCssFile($assetsUrl.'/css/zocial.css');
    ob_start();
		?>
		$(function() {
			$('.hoauthWidget a').click(function() {
				var signinWin;
				var screenX     = window.screenX !== undefined ? window.screenX : window.screenLeft,
					screenY     = window.screenY !== undefined ? window.screenY : window.screenTop,
					outerWidth  = window.outerWidth !== undefined ? window.outerWidth : document.body.clientWidth,
					outerHeight = window.outerHeight !== undefined ? window.outerHeight : (document.body.clientHeight - 22),
					width       = <?=$this->popupWidth?>,
					height      = <?=$this->popupHeight?>,
					left        = parseInt(screenX + ((outerWidth - width) / 2), 10),
					top         = parseInt(screenY + ((outerHeight - height) / 2.5), 10),
					options    = (
					'width=' + width +
					',height=' + height +
					',left=' + left +
					',top=' + top
					);
		 
				signinWin=window.open(this.href,'Login',options);

				if (window.focus) {signinWin.focus()}

				return false;
			});
		});
<?php
    $cs->registerScript(__CLASS__, ob_get_clean());
	}
}
