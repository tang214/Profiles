<?php
require('Autoload.php');

$auth = AuthProvider::getInstance();
$src  = false;
if(isset($_GET['src']))
{
    $src = $_GET['src'];
}
else if(strstr($_SERVER['HTTP_REFERER'], 'google.com') !== false)
{
    $src = 'google';
}

$ref = '.';
if(strstr($_SERVER['HTTP_REFERER'], 'google.com') === false)
{
    $ref = $_SERVER['HTTP_REFERER'];
}

switch($src)
{
    case 'google':
        $google = $auth->getAuthenticator('Auth\GoogleAuthenticator');
        if(!isset($_GET['code']))
        {
            $google->redirect();
            die();
        }
        else
        {
            $res = $google->authenticate($_GET['code'], $current_user);
            switch($res)
            {
                case \Auth\Authenticator::SUCCESS:
                    header('Location: '.$ref);
                    die();
                default:
                case \Auth\Authenticator::LOGIN_FAILED:
                    header('Location: login.php');
                    die();
                case \Auth\Authenticator::ALREADY_PRESENT:
                    header('Location: user_exists.php?src=google&uid='.$current_user['uid']);
                    die();
            }
        }
        break;
}
?>
