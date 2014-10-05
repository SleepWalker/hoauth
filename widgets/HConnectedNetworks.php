<?php
/**
 * HConnectedNetworks shows the list with networks, that user connected to
 *
 * @uses CWidget
 * @version 1.2.5
 * @copyright Copyright &copy; 2013 Sviatoslav Danylenko
 * @author Sviatoslav Danylenko <dev@udf.su>
 * @license MIT ({@link http://opensource.org/licenses/MIT})
 * @link https://github.com/SleepWalker/hoauth
 */

namespace sleepwalker\hoauth\widgets;

class HConnectedNetworks extends \CWidget
{
    public $tag = 'ul';

    public function run()
    {
        // provider delete action
        if (\Yii::app()->request->isPostRequest && isset($_GET['hoauthDelSN'])) {
            while(@ob_end_clean());

            $userNetwork = \sleepwalker\hoauth\models\UserOAuth::model()->findUser(\Yii::app()->user->id, $_GET['hoauthDelSN']);
            if ($userNetwork) {
                $userNetwork->delete();
            }
            Yii::app()->end();
        }

        $userNetworks = \sleepwalker\hoauth\models\UserOAuth::model()->findUser(\Yii::app()->user->id);
        $sns = array();

        foreach ($userNetworks as $network) {
            $deleteUrl = '?hoauthDelSN=' . $network->provider;
            try {
                array_push($sns, array('provider' => $network->provider, 'profileUrl' => $network->profileCache->profileURL, 'deleteUrl' => $deleteUrl));
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        $this->render('networksList', array(
            'sns' => $sns,
        ));
    }
}
