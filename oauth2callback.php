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
                    header('Location: user_exists.php?src=google&uid='.$current_user->uid);
                    die();
            }
        }
        break;
    case 'twitter':
        $twitter = $auth->getAuthenticator('Auth\TwitterAuthenticator');
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
        $gitlab = $auth->getAuthenticator('Auth\OAuth2\GitLabAuthenticator');
        if(!isset($_GET['code']))
        {
            $google->redirect();
            die();
        }
        else
        {
            $res = $gitlab->authenticate($_GET['code'], $current_user);
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
                    header('Location: user_exists.php?src=gitlab&uid='.$current_user->uid);
                    die();
            }
        }
    //Generic OAuth...
    default:
        print_r($_SERVER);
        break;
}
?>
