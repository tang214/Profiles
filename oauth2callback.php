<?php
require('Autoload.php');

function doAuthByType($type, $src, $auth, $ref)
{
    $currentUser = false;
    $google = $auth->getMethodByName($type);
    if(!isset($_GET['code']))
    {
        $google->redirect();
        die();
    }
    else
    {
        $res = $google->authenticate($_GET['code'], $currentUser);
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
                header('Location: user_exists.php?src='.$src.'&uid='.$currentUser->uid);
                die();
        }
    }
}

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
else if(strstr($_SERVER['HTTP_REFERER'], 'gitlab.com') !== false)
{
    $src = 'gitlab';
}

$ref = '.';
if(isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], 'google.com') === false)
{
    $ref = $_SERVER['HTTP_REFERER'];
}

switch($src)
{
    case 'google':
        doAuthByType('Auth\GoogleAuthenticator', $src, $auth, $ref);
        break;
    case 'twitter':
        $twitter = $auth->getMethodByName('Auth\TwitterAuthenticator');
        if(!isset($_GET['oauth_token']) || !isset($_GET['oauth_verifier']))
        {
            $twitter->redirect();
            die();
        }
        else
        {
            $twitter->authenticate($_GET['oauth_token'], $_GET['oauth_verifier'], $current_user);
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
                    header('Location: user_exists.php?src=twitter&uid='.$current_user->uid);
                    die();
            }
        }
        break;
    case 'gitlab':
        doAuthByType('Auth\OAuth2\GitLabAuthenticator', $src, $auth, $ref);
        break;
        //Generic OAuth...
    default:
        print_r($_SERVER);
        break;
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
