<?php
/**
 * HOAuthAction - this the main class in hoauth extension.
 * 
 * @uses CAction
 * @version 1.2.5
 * @copyright Copyright &copy; 2013 Sviatoslav Danylenko
 * @author Sviatoslav Danylenko <dev@udf.su> 
 * @license MIT ({@link http://opensource.org/licenses/MIT})
 * @link https://github.com/SleepWalker/hoauth
 */

/**
 * HOAuthAction provides simple integration with social network authorization lib Hybridauth in Yii.
 *
 * HOAuthAction requires, that your user model implements findByEmail() method, that should return user model by its email.
 *
 * Avaible social networks: 
 *    OpenID, Google, Facebook, Twitter, Yahoo, MySpace, Windows Live, LinkedIn, Foursquare, AOL
 * Additional social networks can be found at: {@link http://hybridauth.sourceforge.net/download.html}
 *
 * Social Auth widget:
 *    <?php $this->widget('ext.hoauth.HOAuthWidget', array(
 *      'controllerId' => 'site', // id of controller where is your oauth action (default: site)
 *    )); ?>
 * uses a little modified Zocial CSS3 buttons: {@link https://github.com/samcollins/css-social-buttons/}
 */


Yii::setPathOfAlias('hoauth', dirname(__FILE__));

class HOAuthAction extends CAction
{
	/**
	 * @var boolean $enabled defines whether the ouath functionality is active. Useful for example for CMS, where user can enable or disable oauth functionality in control panel
	 */
	public $enabled = true;

	/**
	 * @var string $model yii alias for user model (or class name, when this model class exists in import path)
	 */
	public $model = 'User';

	/**
	 * @var array $attributes attributes synchronization array (user model attribute => oauth attribute). List of available profile attributes you can see at {@link http://hybridauth.sourceforge.net/userguide/Profile_Data_User_Profile.html "HybridAuth's Documentation"}.
	 *
	 * Additional attributes:
	 *    birthDate - The full date of birthday, eg. 1991-09-03
	 *    genderShort - short representation of gender, eg. 'm', 'f'
	 *
	 * You can also set attributes, that you need to save in model too, eg.:
	 *    'attributes' => array(
	 *      'is_active' => 1,
	 *      'date_joined' => new CDbExpression('NOW()'),
	 *    ),
	 *
	 * @see HOAuthAction::$avaibleAtts
	 */
	public $attributes = array();

	/**
	 * @var string $scenario scenario name for the $model (optional)
	 */
	public $scenario = 'insert';

	/**
	 * @var string $loginAction name of a local login action
	 */
	public $loginAction = 'actionLogin';

	/**
	 * @var integer $duration how long the script will remember the user
	 */
	public $duration = 2592000; // 30 days

	/**
	 * @var boolean $useYiiUser enables support of Yii user module
	 */
	public static $useYiiUser;

	/**
	 * @var boolean $alwaysCheckPass flag to control password checking for the scenario, 
	 *      when when social network returned email of existing local account. If set to
	 *      `false` user will be automatically logged in without confirming account with password
	 */
	public $alwaysCheckPass = true;

	/**
	 * @var string $userIdentityClass UserIdentity class that will be used to log user in.
	 */
	public $userIdentityClass = 'UserIdentity';

	/**
	 * @var string $usernameAttribute you can specify the username attribute, when user must fill it
	 */
	public $usernameAttribute = false;

	/**
	 * @var boolean $useUserReturnUrl enable/disable usage of CWebUser::returnUrl
	 */
	public $useUserReturnUrl = true;

	/**
	 * @var string|array $oauthReturnUrl specific for oauth return url
	 */
	public $oauthReturnUrl = false;

	/**
	 * @var string $returnUrlStateId the id that should be used to store state for hoauth return url
	 */
	public static $oauthReturnUrlStateId = '__oauthReturnUrl';

	/**
	 * @var string $_emailAttribute
	 */
	protected $_emailAttribute = false;

	/**
	 * @var array $avaibleAtts Hybridauth attributes that support by this script (this a list of all available attributes in HybridAuth 2.0.11) + additional attributes (see $attributes property)
	 */
	protected $_avaibleAtts = array('identifier', 'profileURL', 'webSiteURL', 'photoURL', 'displayName', 'description', 'firstName', 'lastName', 'gender', 'language', 'age', 'birthDay', 'birthMonth', 'birthYear', 'email', 'emailVerified', 'phone', 'address', 'country', 'region', 'city', 'zip', 'birthDate', 'genderShort');

	/**
	 * @var UserOAuth the model to handle the work during the request
	 */
	protected $_oauth;

	public function run()
	{		
		if($this->enabled) {
			$this->setUp();

			if(isset($_GET['provider'])) {
				// after oauth — working with user model and his data from SN
				Yii::import('hoauth.models.*');
				$this->oAuth($_GET['provider']);
			} else {
				// Handling OAuth (redirects, tokens etc.)
				$path = dirname(__FILE__);
				require($path.'/hybridauth/index.php');
				Yii::app()->end();
			}
		}

		Yii::app()->controller->{$this->loginAction}();
	}

	/**
	 * Initiates authorization with specified $provider and 
	 * then authenticates the user, when all goes fine
	 * 
	 * @param mixed $provider provider name for HybridAuth
	 * @access protected
	 * @return void
	 */
	protected function oAuth($provider)
	{
		try{
			// trying to authenticate user via social network
			$oAuth = UserOAuth::model()->authenticate($provider);
			$this->setOauth($oAuth);

			$accessCode = $this->getAccessCode();

			if($accessCode === 1) {
				// the authentication was successfull. closing auth window
				?>
				<script>
					var returnUrl = <?php echo $this->useUserReturnUrl ? CJavaScript::encode(Yii::app()->user->getReturnUrl(false)) : false; ?>;
					if(window.opener) {
						if(returnUrl) {
							window.opener.location.href = returnUrl;
						} else {
							window.opener.location.reload();
						}

						window.close();
					}else{
						window.location.href = returnUrl ? returnUrl : '/';
					}
				</script>
				<?php
				Yii::app()->end();
			}
		} catch(Exception $e) {
			$this->handleError($e);
		}
		?>
		<script>
			window.close();
		</script>
		<?php
	}

	/**
	 * @param  UserOAuth $oAuth
	 * @return integer        access code
	 *     $accessCode == 0 - user shouldn't get access
	 *     $accessCode == 1 - user may login
	 *     $accessCode == 2 - user may login, but not now (e.g. the email should be verified and activated)
	 */
	protected function getAccessCode()
	{
		$oAuth = $this->oAuth;
		// If we already have a user logged in, associate the authenticated 
		// provider with the logged-in user
		if(!Yii::app()->user->isGuest) {
			$accessCode = 1;
			$oAuth->bindTo(Yii::app()->user->id);
		} else {
			list($user, $isNewUser) = $this->prepareGuestUser();

			// checking if current user is not banned or anything else
			$accessCode = 1;
			if(method_exists($this->controller, 'hoauthCheckAccess')) {
				$accessCode = $this->controller->hoauthCheckAccess($user);
			} elseif($this->useYiiUser) {
				$accessCode = $this->yiiUserCheckAccess($user);
			}

			// sign user in
			switch($accessCode) {
				case 1:
					if(!$oAuth->bindTo($user->primaryKey)) {
						throw new Exception("Error, while binding user to provider:\n\n" . var_export($oAuth->errors, true));
					}

					$identity = $this->useYiiUser
						? new DummyUserIdentity($user->primaryKey, $user->email)
						: new $this->userIdentityClass($user->email, null);

					if(!Yii::app()->user->login($identity,$this->duration)) {
						throw new Exception("Can't sign in, something wrong with UserIdentity class.");
					}

					// user was successfully logged in
					// firing callback
					if(method_exists($this->controller, 'hoauthAfterLogin')) {
						$this->controller->hoauthAfterLogin($user, $isNewUser);
					}
					break;
					
				case 2: // stopping script to let checkAccess() function render new content
					Yii::app()->end();
					break;

				default:
					throw new Exception("Something wrong. You can not log in.");
			}
		}

		return $accessCode;
	}

	/**
	 * Prepares the model of the guest user,
	 * registers new user, if needed
	 * 
	 * @return array    of two elements:
	 *                  - user model
	 *                  - whether it is the new user
	 */
	protected function prepareGuestUser()
	{
		$isNewUser = false;
		$userProfile = $this->oAuth->profile;

		if($this->oAuth->isBond) {
			// this social network account is bond to existing local account
			Yii::log("Logged in with existing link with '{$this->oAuth->provider}' provider", CLogger::LEVEL_INFO, 'hoauth.'.__CLASS__);
			$user = $this->userModel->findByPk($this->oAuth->user_id);

			return array($user, $isNewUser);
		} elseif(!empty($userProfile->emailVerified)) { // SN returned user email
			// checking whether we already have a user with specified email
			if($this->useYiiUser) {
				$user = $this->userModel->findByAttributes(array('email' => $userProfile->emailVerified));
			} else {
				$user = $this->userModel->findByEmail($userProfile->emailVerified);
			}
		}

		if(!isset($user)) {
			// registering a new user
			$user = new $this->model($this->scenario);
			$isNewUser = true;
		}

		if($this->alwaysCheckPass || $isNewUser) {
			if(method_exists($this->controller, 'hoauthProcessUser')) {
				$user = $this->controller->hoauthProcessUser($user, $isNewUser);
			} else {
				$user = $this->processUser($user, $userProfile);
			}
		}

		return array($user, $isNewUser);
	}
	
	/**
	 * Handles two cases:
	 * 	- new user (registers new user, collects username and email if needed)
	 * 	- the case, when social network returned email of existed user and
	 * 	  he is logging the first time with this SN. We will ask him for password to validate, that it is realy his account
	 *
	 * @param CActiveRecord $user current user model
	 * @param object $userProfile social network's user profile object
	 * @access protected
	 */
	protected function processUser($user, $userProfile)
	{
		if($this->useYiiUser) {
			$profile = new Profile();
			// enabling register mode
			// old versions of yii
			$profile->regMode = true;
			// new version, when regMode is static property
			$prop = new ReflectionProperty('Profile', 'regMode');
			if($prop->isStatic())
				Profile::$regMode = true;
		}

		if($user->isNewRecord) { // the old user should manage his profile by himself
			$this->populateModel($user, $userProfile);
		}

		// checking if we have all data to register new user
		// if SN returned email of existed user, check that he knows the password
		$user = $this->checkUserData($user);

		// the model won't be new, if user provided email and password of existing account
		if($user->isNewRecord) {
			if(!$user->save()) {
				throw new Exception("Error, while saving {$this->model} model:\n\n" . var_export($user->errors, true));
			}

			if($this->useYiiUser) {
				$profile->user_id = $user->primaryKey;
				if($profile->hasAttribute('firstname')) {
					// we have an older yii-user version or the db dump from `data` directory instead of migration was used
					$profile->firstname = $userProfile->firstName;
					$profile->lastname = $userProfile->lastName;
				} else {
					$profile->first_name = $userProfile->firstName;
					$profile->last_name = $userProfile->lastName;
				}

				if(!$profile->save()) {
					throw new Exception("Error, while saving " . get_class($profile) . " model:\n\n" . var_export($profile->errors, true));
				}
			}

			// trying to send activation email
			$this->sendActivationEmail($user);
		}

		return $user;
	}

	/**
	 * Uses HUserInfoForm to check if we have all data, that we need from new user
	 * displays the form to get the required, but not specified user data
	 * 
	 * @param  CActiveRecord $user user model
	 * @return CActiveRecord user model with correct data
	 */
	protected function checkUserData($user)
	{
		// trying to fill email and username fields
		// NOTE: we display `username` field in our form only if it is required by the model
		if($this->usernameAttribute && !$user->isAttributeRequired($this->usernameAttribute)) {
			$this->usernameAttribute = false;
		}

		$form = new HUserInfoForm($user, $this->_emailAttribute, $this->usernameAttribute);

		if(!$form->validateUser()) {
			// We need to request some info from user
			$this->controller->render('hoauth.views.form', array(
				'form' => $form,
				));
			Yii::app()->end();
		}

		// updating attributes in $user model (if needed)
		$user = $form->validUserModel;

		return $user;
	}

	/**
	 * Sends email activation email, when it is needed	
	 * 
	 * @param CActiveRecord $user current user model
	 * @access protected
	 * @return void
	 */
	protected function sendActivationEmail($user)
	{
		if($this->useYiiUser) {
			// why not to put this code not in controller, but in the User model of `yii-user` module?
			// for now I can only copy-paste this code from controller...
			if (Yii::app()->getModule('user')->sendActivationMail) {
				$activation_url = Yii::app()->createAbsoluteUrl('/user/activation/activation',array("activkey" => $user->activkey, "email" => $user->email));
				UserModule::sendMail($user->email,UserModule::t("You registered on {site_name}",array('{site_name}'=>Yii::app()->name)),UserModule::t("To activate your account, please go to {activation_url}",array('{activation_url}'=>$activation_url)));
			}
		} else {
			if(method_exists($user, 'sendActivationEmail')) {
				$user->sendActivationEmail();
			} elseif(method_exists($user, 'sendActivationMail')) { // TODO: delete in future
				$user->sendActivationMail();
			}
		}
	}

	/**
	 * Populates User model with data from social network profile
	 * 
	 * @param CActiveRecord $user users model
	 * @param mixed $profile HybridAuth user profile object
	 * @access protected
	 */
	protected function populateModel($user, $profile)
	{
		foreach($this->attributes as $attribute => $pAtt) {
			if(in_array($pAtt, $this->_avaibleAtts)) {
				switch($pAtt) {
					case 'genderShort':
						$gender = array('female'=>'f','male'=>'m');
						$att = $gender[$profile->gender];
						break;
					case 'birthDate':
						$att = $profile->birthYear 
						? sprintf("%04d-%02d-%02d", $profile->birthYear, $profile->birthMonth, $profile->birthDay)
						: null;
						break;
					case 'email':
						$att = $profile->emailVerified;
						break;
					default:
						$att = $profile->$pAtt;
				}
				if(!empty($att)) {
					$user->$attribute = $att;
				}
			} else {
				$user->$attribute = $pAtt;
			}
		}
	}

	/**
	 * Checks whether the $user can be logged in
	 *
	 * @param CActiveRecord $user current `yii-user` user's model
	 * @param boolean $render flag that enables rendering
	 */
	protected function yiiUserCheckAccess($user, $render = true)
	{
		if($user->status==0&&Yii::app()->getModule('user')->loginNotActiv==false) {
			$error = UserIdentity::ERROR_STATUS_NOTACTIV;
			$return = 2;
		} elseif($user->status==-1) {
			$error = UserIdentity::ERROR_STATUS_BAN;
			$return = 0;
		} else {
			$error = UserIdentity::ERROR_NONE;
			$return = 1;
		}

		if($error && $render) {
			$this->controller->render('hoauth.views.yiiUserError', array(
				'errorCode' => $error,
				'user' => $user,
				));
		}

		return $return;
	}

	/**
	 * Checks if the action was properly setup and ready to run
	 * @throws Exception If improperly setted up
	 */
	protected function setUp()
	{
		if(!is_array($this->attributes)) {
			$this->attributes = array();
		}

		if(!isset(self::$useYiiUser)) {
			self::$useYiiUser = file_exists(Yii::getPathOfAlias('user.components') . '/UWrelBelongsTo.php');
		}

		// checking if we have `yii-user` module (I think that `UWrelBelongsTo` is unique class name from `yii-user`)
		if(self::$useYiiUser === true) {
			// setting up yii-user's user model
			Yii::import('application.modules.user.models.*');
			Yii::import('hoauth.DummyUserIdentity');

			// preparing attributes array for `yii-user` module
			$this->attributes = CMap::mergeArray(array(
				'email' => 'email',
				'username' => 'displayName',
				'status' => User::STATUS_ACTIVE,
				), $this->attributes);

			$this->usernameAttribute = 'username';
			$this->_emailAttribute = 'email';
		} else {
			if(!in_array('email', $this->attributes)) {
				throw new CException("You forgot to bind 'email' field in " . __CLASS__ . "::attributes property.");
			}

			Yii::import($this->model, true);
			$this->model = substr($this->model, strrpos($this->model, '.'));

			$this->_emailAttribute = array_search('email', $this->attributes);
		}

		if(empty($this->model) || !class_exists($this->model)) {
			throw new CException('You should specify the User model to work with');
		}

		if(!method_exists($this->model, 'findByEmail') && !self::$useYiiUser) {
			throw new Exception("Model '{$this->model}' must implement the 'findByEmail' method");
		}
	}

	/**
	 * @return CActiveRecord user model depending on existence of yii-user
	 */
	public function getUserModel()
	{
		if($this->useYiiUser) {
			$user = User::model();
		} else {
			$user = call_user_func(array($this->model, 'model'));
		}

		return $user;
	}

	public function getUseYiiUser()
	{
		return self::$useYiiUser;
	}

	public function setUseYiiUser($value)
	{
		self::$useYiiUser = $value;
	}

	public function getEmailAttribute()
	{
		return $this->_emailAttribute;
	}

	/**
	 * Sets the UserOAuth model to work with
	 * @param UserOAuth $value model
	 */
	public function setOauth(UserOAuth $value)
	{
		if(!$this->_oauth) {
			$this->_oauth = $value;
		}
	}

	/**
	 * Sets the UserOAuth model to work with
	 * @param UserOAuth $value model
	 */
	public function getOauth()
	{
		return $this->_oauth;
	}

	public static function t($message,$params=array(),$source=null,$language=null)
	{
		return Yii::t('HOAuthAction.root', $message,$params,$source,$language);
	}

	/**
	 * Handles, log or displays errors
	 * @param  Exception $e
	 */
	protected function handleError(Exception $e)
	{
		$error = "";

		// Display the received error
		switch($e->getCode()) { 
			case 0 : $error = "Unspecified error."; throw $e; break;
			case 1 : $error = "Hybriauth configuration error."; break;
			case 2 : $error = "Provider not properly configured."; break;
			case 3 : $error = "Unknown or disabled provider."; break;
			case 4 : $error = "Missing provider application credentials."; break;
			case 5 : $error = "Authentication failed. The user has canceled the authentication or the provider refused the connection."; break;
			case 6 : $error = "User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again."; 
				@$this->oAuth->logout(); 
			break;
			case 7 : $error = "User not connected to the provider."; 
				@$this->logout(); 
			break;
			case 8 : $error = "Provider does not support this feature.";	break;
		}

		$error .= "\n\n<br /><br /><b>Original error message:</b> " . $e->getMessage(); 
		Yii::log(strip_tags($error), CLogger::LEVEL_INFO, 'hoauth.'.__CLASS__);
		if(YII_DEBUG)
			throw $e;
	}
}
