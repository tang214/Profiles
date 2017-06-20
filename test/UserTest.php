<?php
//require_once(dirname(__FILE__).'/../api/v1/users.php');

function validEmail($email)
{
    if(filter_var($email) === false)
    {
        return false;
    }
    $pos = strpos($email, '@');
    if($pos === false)
    {
        return false;
    }
    $domain = substr($email, $pos + 1);
    if(checkdnsrr($domain, 'MX') === false)
    {
        return false;
    }
    return true;
}

class UserTest extends PHPUnit_Framework_TestCase
{
    public function testEmailFunction()
    {
        $this->assertTrue(validEmail('test@test.com'));
        $this->assertFalse(validEmail('test@test'));
        $this->assertFalse(validEmail('test@gmail.x'));
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
