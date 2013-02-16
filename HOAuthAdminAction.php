<?php
/**
 * HOAuthAdminAction - administrative action. Helps to create config for HybridAuth
 * 
 * @uses CAction
 * @version 1.0
 * @copyright Copyright &copy; 2013 Sviatoslav Danylenko
 * @author Sviatoslav Danylenko <dev@udf.su> 
 * @license PGPLv3 ({@link http://www.gnu.org/licenses/gpl-3.0.html})
 */
class HOAuthAdminAction extends CAction
{
  public function run()
  {
    $path = dirname(__FILE__);
    include($path.'/hybridauth/install.php');
    Yii::app()->end();
  }
}
