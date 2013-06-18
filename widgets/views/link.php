<?php
/**
 * @var HOAuthWidget $this
 * @var string $provider name of provider
 */

$invitation = Yii::app()->user->isGuest ? HOAuthAction::t('Sign in with') : HOAuthAction::t('Connect with');
?>
<p>
	<a href="<?php echo Yii::app()->createUrl($this->route . '/oauth', array('provider' => $provider)); ?>" class="zocial <?php  echo strtolower($provider) ?>"><?php  echo "$invitation $provider"; ?></a>
</p>
