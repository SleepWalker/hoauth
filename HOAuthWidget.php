<?php
/**
 * HOAuthWidget provides widgets with button for login with social networs
 * 
 * @uses CWidget
 * @version 1.2
 * @copyright Copyright &copy; 2013 Sviatoslav Danylenko
 * @author Sviatoslav Danylenko <dev@udf.su> 
 * @license PGPLv3 ({@link http://www.gnu.org/licenses/gpl-3.0.html})
 */

/**
 * NOTE: If you want to change the order of button it is better to change this order in HybridAuth config.php file
 */
class HOAuthWidget extends CWidget
{
  /**
   * @var string $route id of module and controller (eg. module/controller) for wich to generate oauth urls
   */
  public $route = false;

  public function run()
  {
    if(!$this->route)
      $this->route = $this->controller->module ? $this->controller->module->id . '/' . $this->controller->id : $this->controller->id;
    
    $config = require(dirname(__FILE__).DIRECTORY_SEPARATOR.'hybridauth/config.php');
    $this->registerFiles();
    foreach($config['providers'] as $provider => $settings)
      if($settings['enabled'])
        $this->render('link', array(
          'provider' => $provider,
        ));


  }

  protected function registerFiles()
  {
    $assetsUrl = Yii::app()->getAssetManager()->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets',false,-1,YII_DEBUG);
    Yii::app()->getClientScript()->registerCssFile($assetsUrl.'/css/zocial.css');
  }
}
