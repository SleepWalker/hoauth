<?php
/**
 *
 * @version 1.2.5
 * @copyright Copyright &copy; 2013 Sviatoslav Danylenko
 * @author Sviatoslav Danylenko <dev@udf.su>
 * @license MIT ({@link http://opensource.org/licenses/MIT})
 * @link https://github.com/SleepWalker/hoauth
 */

/**
 * Hybridauth attributes that support by this script:
 * http://hybridauth.sourceforge.net/userguide/Profile_Data_User_Profile.html
 * 
 * Additional attributes:
 *  - birthDate (YYYY-mm-dd)
 *  - genderShort (f or m)
 */

namespace sleepwalker\hoauth\components;

class ProfileAdapter extends \CComponent
{
    private $_profile;

    public function __construct($profile)
    {
        $this->_profile = $profile;
    }

    public function getGenderShort()
    {
        $gender = array('female' => 'f', 'male' => 'm');
        return isset($gender[$this->_profile->gender]) ? $gender[$this->_profile->gender] : null;
    }

    public function getBirthDate()
    {
        return !empty($this->_profile->birthYear)
            ? sprintf("%04d-%02d-%02d", $this->_profile->birthYear, $this->_profile->birthMonth, $this->_profile->birthDay)
            : null;
    }

    public function getEmail()
    {
        return $this->_profile->emailVerified;
    }

    public function __isset($key)
    {
        if (parent::__isset($key)) {
            return true;
        }

        if (isset($this->_profile->$key)) {
            return true;
        }

        return false;
    }

    public function __get($key)
    {
        try {
            return parent::__get($key);
        } catch (\Exception $e) {
            if (isset($this->_profile->$key)) {
                return $this->_profile->$key;
            } else {
                throw new $e;
            }
        }
    }
}
