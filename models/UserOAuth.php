<?php

/**
 * This is the model class for table "user_oauth".
 *
 * The followings are the available columns in table 'user_oauth':
 * @property integer $user_id
 * @property string $name name of provider
 * @property string $value unique user authentication id that was returned by provider
 * @property string $session_data session data with user profile
 */
class UserOAuth extends CActiveRecord
{
  /**
   * @var $_hybridauth HybridAuth class instance
   */
  protected $_hybridauth;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserOAuth the static model class
	 */
	public static function model($className=__CLASS__)
  {
    try
    {
      return parent::model($className);
    }
    catch(CDbException $e)
    {
      $sql = file_get_contents(dirname(__FILE__).'/user_oauth.sql');
      Yii::app()->db->createCommand($sql)->execute();
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
   * @access public
   * @return array of UserOAuth models
   */
  public function findUser($user_id, $provider = false)
  {
    $params = array('user_id' => $user_id);
    if($provider)
      $params['name'] = $provider;
      
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
      $config = $path . '/config.php';

      include($path.'/Hybrid/Auth.php');
      $this->_hybridauth = new Hybrid_Auth( $config );

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
    if(!empty($this->session_data))
      return $this->hybridAuth->getAdapter($this->name);
    else
      return null;
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
