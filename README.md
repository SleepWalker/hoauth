hoauth
======

hoauth extension provides simple integration with social network authorization lib [Hybridauth](http://hybridauth.sourceforge.net) in Yii.

Available social networks
-------------------------

* OpenID
* Google
* Facebook
* Twitter
* Yahoo
* MySpace
* Windows Live
* LinkedIn
* Foursquare
* Vkontakte
* AOL

Additional social networks providers can be found at HybridAuth [website](http://hybridauth.sourceforge.net/download.html)

A little about how it's woks
----------------------------

This extension authenticates and if it's need creates new user. When user was registered "locally" (so he has login (email) and password), then he can also log in with it's social account (extension checks if user with provided email exists in db, when yes, the he will be logged in and it is no matter how had he registered earlier - locally or not). After the user logged in he will be redirected to `Yii::app()->user->returnUrl`.

In future releases, when it will be needed I can implement "classical algorithm": either local authorization or social authorization.

**NOTE:** this extension requires `UserIdentity` class. It doesn't use `authenticate()` method of `UserIdentity` class. Class constructor called with parameters `new UserIdentity($mail, null)` and than called `CWebUser::login()` method (while authentication work did for us social network). When your identity class has another name, you should edit `HOAuthAction` class.

**NOTE 2:** This extension will also automatically create `user_oauth` table in your database. About it see "`UserOAuth` model" section.

Installation and Usage
----------------------

**1\.** Simply copy the files in your `extensions` directory (or in any other directory you want).

**2\.** Edit yours controller source code (eg. `SiteController` class with `actionLogin()` method) to add new actions:
```php
class SiteController extends Controller
{
  /**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			...
      'oauth' => array(
        // the list of additional properties of this action is below
        'class'=>'ext.hoauth.HOAuthAction',
        // Yii alias for your user's model, or simply class name, when it already on yii's import path
        'model' => 'User', 
        // map model attributes to attributes of user's social profile
        // model attribute => profile attribute
        // the list of avaible attributes is below
        'attributes' => array(
          'email' => 'email',
          'fname' => 'firstName',
          'lname' => 'lastName',
          'gender' => 'genderShort',
          'birthday' => 'birthDate',
          // you can also specify additional values, 
          // that will be applied to your model (eg. account activation status)
          'acc_status' => 1,
        ),
      ),
      // this is an admin action that will help you to configure HybridAuth 
      // (you must delete this action, when you'll be ready with configuration, or 
      // specify rules for admin role. User shouldn't have access to this action!)
      'oauthadmin' => array(
        'class'=>'ext.hoauth.HOAuthAdminAction',
      ),
		);
	}
...
}
```

**3\.** Add the `findByEmail` method to your user`s model class:
```php
  /**
   * Returns User model by its email
   * 
   * @param string $email 
   * @access public
   * @return User
   */
  public function findByEmail($email)
  {
    return self::model()->findByAttributes(array('email' => $email));
  }
```

**4\.** Visit your `oauthadmin` action (eg. http://yoursite.com/site/oauthadmin) to create the HybridAuth config. For your oauth `base_url` use this: http://yoursite.com/site/oauth .

**5\.** Add social login widget to your login page view:
```php
<?php $this->widget('ext.hoauth.HOAuthWidget', array(
  // id of controller where is your oauth action is (default: site)
  'controllerId' => 'notsitecontroller', 
)); ?>
```

Available social profile fields
-------------------------------

You can find them at HybridAuth [website](http://hybridauth.sourceforge.net/userguide/Profile_Data_User_Profile.html).
And here is some additional fields, that I needed in my project, you can use them too:
* `birthDate` - The full date of birthday (eg. 1991-09-03)
* `genderShort` - short representation of gender (eg. 'm', 'f')

Additional properties for `HOAuthAction`
----------------------------------------
* `enabled` - defines whether the ouath functionality is active. Useful for example for CMS, where user can enable or disable oauth functionality in control panel. (default: true)
* `scenario` - scenario name for the $model (optional)
* `loginAction` - name of a local login action (should be in the same controller as `oauth` action). (default: 'actionLogin')
* `duration` - 'remember me' duration in ms. (default: 2592000 //30days)

`UserOAuth` model
-----------------

`UserOAuth` model used to bind social services to user's account and to store session with social network profile. If you want to use this data (user profile) later, please use `UserOAuth::getHybridAuth()` (It returns `Hybrid_Auth` object and restores session, when this is possible) or `UserOAuth::getAdapter()` (returns Adapter class, when we have user's HybridAuth `session_data`) method:
```php
$userOAuths = UserOAuth::findUser(5); // find all authorizations from user with id=5
foreach($userOAuths as $userOAuth)
{
  $profile = $userOAuth->adapter->getUserProfile();
  echo "Your email is {$profile->email} and social network - {$userOAuth->name}<br />";
}
```
or
```php
$userOAuth = UserOAuth::findUser(5, "Google"); // find all authorizations from user with id=5
$profile = $userOAuth->adapter->getUserProfile();
echo "Your email is {$profile->email} and social network - {$userOAuth->name}<br />";
```
About how to use HybridAuth object you can read [here](http://hybridauth.sourceforge.net/userguide.html).

Opensource projects that used in this extension
-----------------------------------------------

* [HybridAuth] (http://hybridauth.sourceforge.net)
* [Zocial CSS3 Buttons] (https://github.com/samcollins/css-social-buttons/)
