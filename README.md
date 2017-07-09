hoauth v1.2.5
=============

* `hoauth` extension provides a simple integration with social network authorization lib [Hybridauth](http://hybridauth.sourceforge.net/) in Yii. (facebook, google, twitter, vkontakte and much more).
* Automatically finds and supports `yii-user` module ([instruction for yii-user](https://github.com/SleepWalker/hoauth/wiki/%5Binstall%5D-hoauth-and-yii-user-extension)).
* supports prefixed tables
* Supports I18N ([available translations](https://github.com/SleepWalker/hoauth/tree/master/messages))

###[Demo](http://hoauth.hamstercms.com/yii-user/) | [Demo Source Code](https://github.com/SleepWalker/hoauth-demo-site)

Requirements
------------
* Yii 1.1.9 or above. (I have tested it only in 1.1.13)

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

Additional social networks providers can be found at HybridAuth [website](http://hybridauth.sourceforge.net/download.html). And how to configure them [here](http://hybridauth.sourceforge.net/userguide.html) at the bottom of the page.

A little about how it works
---------------------------

The users of `yii-user` extension can skip this section, because `hoauth` will do all the stuff automatically.
This extension authenticates and if it's needed, creates a new user. When the user has been registered "locally" (so it has a login (email) and a password), then it can also log in with its social account (extension checks whether a user with the provided email exists in db, and if the user exists, it will be logged in and it is no matter how had he registered earlier - locally or not). After the user logged in, it will be redirected to `Yii::app()->user->returnUrl`.

This extension requires `UserIdentity` class, but it doesn't use `authenticate()` method of `UserIdentity` class. The class' constructor is called with following parameters `new UserIdentity($mail, null)` and `CWebUser::login()` method is called then (while authentication work did for us social network). If social network doesn't give us the user's email, **hoauth** will ask the user for the email, and when the email exists in our db, the password will be asked too. At the end, we bind a unique user identifier that is provided by the social network to the user id for future's sign in. [Example of UserIdentity class](https://github.com/SleepWalker/hoauth/wiki/UserIdentity-class-example).

If you need to perform some access checks for the user, you can use `hoauthCheckAccess($user)` callback (simply create new method in controller where you added `HOAuthAction`). This method will be called with one input argument - *User model* of the user being authorized. This method should return integer values (`accessCode`) depending on the scenario needed:
* 0 - user shouldn't get access
* 1 - user may login
* 2 - user may login, but not now (e.g. the email should be verified and activated)
You can also not only return the `accessCode`, but also render the page with error or any information you need.

**NOTE:** This extension will automatically create `user_oauth` table in your database. See "`UserOAuth` model" section.

Installation and Usage
----------------------

* [instruction for yii-user](https://github.com/SleepWalker/hoauth/wiki/%5Binstall%5D-hoauth-and-yii-user-extension)

**1\.** Create a new directory called `hoauth` in your `extensions` directory (or any other directory you prefer) and copy the content files there.
A directory structure example:
```php
/protected/
   extesions/
      hoauth/
         hybridauth/
         messages/
         models/
         views/
         widgets/
         .gitignore
         CHANGELOG
         DummyUserIdentity.php
         HOAuthAction.php
         HOAuthAdminAction.php
         MIT-LICENSE.txt
         README.md
         UPGRADE.md
```

**2\.** Edit your controller source code (eg. `SiteController` class with `actionLogin()` method) to add new actions:
```php
class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
      'oauth' => array(
        // the list of additional properties of this action is below
        'class'=>'ext.hoauth.HOAuthAction',
        // Yii alias for your user's model, or simply class name, when it already on yii's import path
        // default value of this property is: User
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

**4\.** Run your `oauthadmin` action (eg. visit http://yoursite.com/site/oauthadmin) to create the HybridAuth config. For your `HybridAuth Endpoint URL` use this: http://yoursite.com/site/oauth. After the installation, you can keep `install.php` in your file system, while it's in Yii protected directory. But you must **remove** `oauthadmin` action, or make such rules, that give access for admin users only. The config file can be found at `application.config.hoauth`

**5\.** Add social login widget to your login page view (you can use `route` property, when you placing your widget not in the same module/controller as your `oauth` action):
```php
<?php $this->widget('ext.hoauth.widgets.HOAuth'); ?>
```
This widget can be switched to the icon view using `onlyIcons` property. It may also be useful for the properties to adjust popup window size: `popupWidth` and `popupHeight`. See `HOAuth.php` file for details.

**Optional:**
**6\.** When you're planning to use social networks that return no email from the user profile (like **Twitter**), you should declare `verifyPassword($password)` or `validatePassword($password)` method in `User` model, that should take the password (not hash) and return `true` if it is valid.
**7\.** You can also declare the `sendActivationMail()` method, that should mark the user's account as inactive and send the mail for activation. This method, when it exists will be used for social networks like **Twitter**, that give us no information about the user's email (because we need to proof that the user has entered using the right email).

Note
----
If you want to display the facebook popup correctly, you should add `"display" => "popup"` to the Facebook configuration's array in `protected/config/hoauth.php`. E.g.:
```php
...
"Facebook" => array ( 
  "enabled" => true,
  "keys"    => array ( "id" => "PUT_YOURS_HERE", "secret" => "PUT_YOURS_HERE" ), 
  "scope"   => "email, user_about_me, user_birthday, user_hometown", // you can change the data, that will be asked from user
  "display" => "popup" // <- this one
)
...
```

Available social profile fields
-------------------------------

You can find them at HybridAuth [website](http://hybridauth.sourceforge.net/userguide/Profile_Data_User_Profile.html).
And here are some additional fields, that I personally needed in my project, but you can use them too:
* `birthDate` - The full date of birthday (eg. 1991-09-03)
* `genderShort` - short representation of gender (eg. 'm', 'f')

Additional properties for `HOAuthAction`
----------------------------------------
* `useYiiUser` - enables support for `yii-user` (default: false). `hoauth` will find `yii-user` module automatically, so you can keep this property as default. You may also keep `attributes` and `model` properties as default.
* `enabled` - defines whether the ouath functionality is active. For example, it is useful for CMS, where the user can enable or disable oauth functionality in the control panel. (default: true)
* `scenario` - scenario name for the $model (optional)
* `loginAction` - name of a local login action (should be in the same controller as `oauth` action). (default: 'actionLogin')
* `duration` - 'remember me' duration in ms. (default: 2592000 //30days)
* `usernameAttribute` - you can specify username attribute, when it has to be unique (like in `yii-user` extension), that hoauth will try to validate its uniqueness.
* `alwaysCheckPass` - flag to control the password checking for the scenario, when the social network has returned the email of an existing local account. If it is set to `false`, the user will be automatically logged in without confirming account with password. (default: `true`)

Available Callbacks
-------------------
To make you able to customize the behavior of `hoauth`, it provides some useful callbacks. Here is the list of them:
* `Controller::hoauthCheckAccess($user)`
* `Controller::hoauthAfterLogin($user)`
* `User::findByEmail($email`
* `User::verifyPassword($password)` or `User::validatePassword($password)`
* `User::sendActivationMail())`

[More about callbacks](https://github.com/SleepWalker/hoauth/wiki/Callbacks)

`UserOAuth` model
-----------------

`UserOAuth` model is used to bind social services to the user's account and to store session with a social network profile. If you want to use this data (the user's profile) later, please use `UserOAuth::getProfile()` method:
```php
$userOAuths = UserOAuth::model()->findUser(5); // find all authorizations from user with id=5
foreach($userOAuths as $userOAuth)
{
  $profile = $userOAuth->profile;
  echo "Your email is {$profile->email} and social network - {$userOAuth->provider}<br />";
}
```
or
```php
$userOAuth = UserOAuth::model()->findUser(5, "Google"); // find all authorizations from user with id=5
$profile = $userOAuth->profile;
echo "Your email is {$profile->email} and social network - {$userOAuth->provider}<br />";
```
You can also use `UserOAuth::profileCache` property to access the cached copy of the profile without making any request to the social network.
[Here](http://hybridauth.sourceforge.net/userguide.html) you can read about using HybridAuth object.

Documentation
-------------
 * [`yii-user` installation guide](https://github.com/SleepWalker/hoauth/wiki/%5Binstall%5D-hoauth-and-yii-user-extension)
 * [Creating yii app with `yii-user` and `hoauth` from scratch by Serge Ponomaryov (@begemotik on yii's site) ](http://www.ponomaryov.org/yii-tutorials/beginners-tutorial-integrating-user-management-and-oauth-into-a-yii-project/)
 * [The source code of demo project](https://github.com/SleepWalker/hoauth-demo-site)
 * [`UserIdentity` class example](https://github.com/SleepWalker/hoauth/wiki/UserIdentity-class-example)
 * [Available Callbacks](https://github.com/SleepWalker/hoauth/wiki/Callbacks)

Sources
-------
* [HybridAuth](http://hybridauth.sourceforge.net)
* [Zocial CSS3 Buttons](https://github.com/samcollins/css-social-buttons/)
* [Project page on Yii's website] (http://yiiframework.com/extension/hoauth/)
