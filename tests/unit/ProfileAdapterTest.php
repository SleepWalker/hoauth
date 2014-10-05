<?php

namespace sleepwalker\hoauth\tests\unit;

class ProfileAdapterTest extends \sleepwalker\hoauth\tests\HTestCase
{
    public function testIsset()
    {
        $profileData = (object)array(
            'foo' => 'bar',
            );

        $profile = new \sleepwalker\hoauth\components\ProfileAdapter($profileData);

        $this->assertTrue(isset($profile->foo));
    }

    public function testGetEmail()
    {
        $profileData = (object)array(
            'emailVerified' => 'bar',
            );

        $profile = new \sleepwalker\hoauth\components\ProfileAdapter($profileData);

        $this->assertEquals($profileData->emailVerified, $profile->email);
    }

    public function testGetterPriority()
    {
        $profileData = (object)array(
            'emailVerified' => 'bar',
            'email' => 'foo',
            );

        $profile = new \sleepwalker\hoauth\components\ProfileAdapter($profileData);

        $this->assertEquals($profileData->emailVerified, $profile->email, 'Class getters should have higher priority');
    }

    public function testNoBirthdate()
    {
        $profileData = (object)array(
            'birthYear' => '',
            'birthMonth' => '',
            'birthDay' => '',
            );

        $profile = new \sleepwalker\hoauth\components\ProfileAdapter($profileData);
        
        $this->assertNull($profile->birthDate);
    }

    public function testBirthdate()
    {
        $profileData = (object)array(
            'birthYear' => '1234',
            'birthMonth' => '1',
            'birthDay' => '10',
            );

        $profile = new \sleepwalker\hoauth\components\ProfileAdapter($profileData);
        
        $this->assertEquals('1234-01-10', $profile->birthDate);
    }

    public function testNoGenderShort()
    {
        $profileData = (object)array(
            'gender' => '',
            );

        $profile = new \sleepwalker\hoauth\components\ProfileAdapter($profileData);
        
        $this->assertNull($profile->genderShort);
    }

    public function testGenderShortFemale()
    {
        $profileData = (object)array(
            'gender' => 'female',
            );

        $profile = new \sleepwalker\hoauth\components\ProfileAdapter($profileData);
        
        $this->assertEquals('f', $profile->genderShort);
    }

    public function testGenderShortMale()
    {
        $profileData = (object)array(
            'gender' => 'male',
            );

        $profile = new \sleepwalker\hoauth\components\ProfileAdapter($profileData);
        
        $this->assertEquals('m', $profile->genderShort);
    }
}
