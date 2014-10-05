<?php
/**
 * The base class for all `hoauth` tests
 */
namespace sleepwalker\hoauth\tests;

class HTestCase extends \CTestCase
{
    /**
     * helper method to get protected methods for unit testing
     * $method->invokeArgs($obj, array()); // $obj is instance of $class
     */
    public static function getMethod($class, $methodName)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Shortcut for getMethod()
     * @param  [type] $obj    [description]
     * @param  [type] $method [description]
     * @param  array  $args   [description]
     * @return [type]         [description]
     */
    public static function inv($obj, $method, $args = array())
    {
        $m = self::getMethod(get_class($obj), $method);
        return $m->invokeArgs($obj, array());
    }
}
