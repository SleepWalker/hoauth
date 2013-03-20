<?php

/**
 * This is the model class for table "user_oauth".
 *
 * The followings are the available columns in table 'user_oauth':
 * @property integer $user_id
 * @property string $provider name of provider
 * @property string $identifier unique user authentication id that was returned by provider
 * @property string $session_data session data with user profile
 */
class UserOAuth extends CActiveRecord
{
  /**
   * @var $_hybridauth HybridAuth class instance
   */
  protected $_hybridauth;

  /**
   * @var $_adapter HybridAuth adapter  
   */
  protected $_adapter;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserOAuth the static model class
	 */
	public static function model($className=__CLASS__)
  {
    try
    {
      $model = parent::model($className);

      // the try statement to correct my stupid column names in v1.0.1 of hoauth
      // sory about this
      try
      {
        // TODO: delete this in next versions
        $model->provider=$model->provider;
      }
      catch(Exception $e)
      {
        ob_start();
?>
ALTER TABLE  `user_oauth` CHANGE  `name`  `provider` VARCHAR( 45 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `value`  `identifier` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
<?php
        Yii::app()->db->createCommand(ob_get_clean())->execute();
        Yii::app()->controller->refresh();
      }
      return $model;
    }
    catch(CDbException $e)
    {
      return self::createDbTable();
    }
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user_oauth';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
		);
  }

  /**
   * @static
   * @access public
   * @return configuration array of HybridAuth lib
   */
  public static function getConfig()
  {
    $yiipath = Yii::getPathOfAlias('application.config');
    $config = $yiipath . '/hoauth.php';

    if(!file_exists($config))
    {
      $oldConfig = dirname(__FILE__) . '/../hybridauth' . '/config.php';

      if(file_exists($oldConfig))
      {
        // TODO: delete this in next versions
        if (is_writable($yiipath) && is_writable($oldConfig)) // trying to move old config to the new dir
          rename($oldConfig, $config);
        else
          $config = $oldConfig;
      }
      else
        throw new CException("The config.php file doesn't exists");
    }

    return require($config);
  }
  
  /**
   * @access public
   * @return array of UserOAuth models
   */
  public function findUser($user_id, $provider = false)
  {
    $params = array('user_id' => $user_id);
    if($provider)
    {
      $params['provider'] = $provider;
      return $this->findByAttributes($params);
    }
    else
      return $this->findAllByAttributes($params);
  }

  /**
   * @access public
   * @return Auth class. With restored users authentication session data
   * @link http://hybridauth.sourceforge.net/userguide.html
   * @link http://hybridauth.sourceforge.net/userguide/HybridAuth_Sessions.html
   */
  public function getHybridAuth()
  {
    if(!isset($this->_hybridauth))
    {
      $path = dirname(__FILE__) . '/../hybridauth';

      require_once($path.'/Hybrid/Auth.php');
      $this->_hybridauth = new Hybrid_Auth( self::getConfig() );

      if(!empty($this->session_data))
        $this->_hybridauth->restoreSessionData($this->session_data);
    }

    return $this->_hybridauth;
  }

  /**
   * @access public
   * @return Adapter for current provider or null, when we have no session data.
   * @link http://hybridauth.sourceforge.net/userguide.html
   */
  public function getAdapter()
  {
    if(!isset($this->_adapter) && isset($this->session_data) && isset($this->provider))
      $this->_adapter = $this->hybridAuth->getAdapter($this->provider);

    return $this->_adapter;
  }

  /**
   * authenticates user by specified adapter  
   * 
   * @param string $provider 
   * @access public
   * @return void
   */
  public function authenticate($provider)
  {
    if(empty($this->provider))
    {
      $this->_adapter = $this->hybridauth->authenticate($provider);
      $this->provider = $provider;
      $this->identifier = $this->profile->identifier;
      $oAuth = self::model()->findByPk(array('provider' => $this->provider, 'identifier' => $this->identifier));
      if($oAuth)
        $this->setAttributes($oAuth->attributes, false);
      else
        $this->isNewRecord = true;

      $this->session_data = $this->hybridauth->getSessionData();
      return $this;
    }

    return null;
  }

  /**
   * @access public
   * @return Hybrid_User_Profile user social profile object
   */
  public function getProfile()
  {
    return $this->adapter->getUserProfile();
  }

  /**
   * binds local user to current provider 
   * 
   * @param mixed $user_id id of the user
   * @access public
   * @return whether the model successfully saved
   */
  public function bindTo($user_id)
  {
    $this->user_id = $user_id;
    return $this->save();
  }

  /**
   * @access public
   * @return whether this social network account bond to existing local account
   */
  public function getIsBond()
  {
    return !empty($this->user_id);
  }

  /**
   * creates table for holding provider bindings  
   */
  protected static function createDbTable()
  {
    $sql = file_get_contents(dirname(__FILE__).'/user_oauth.sql');
    Yii::app()->db->createCommand($sql)->execute();
    return parent::model(__CLASS__);
  }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
    return array(
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}
}
