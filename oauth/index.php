<?php
require_once('class.FlipREST.php');
require_once('class.AuthProvider.php');

if($_SERVER['REQUEST_URI'][0] == '/' && $_SERVER['REQUEST_URI'][1] == '/')
{
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 1);
}

$app = new FlipREST();
$app->get('/authorize', 'oauth_login');
$app->post('/access_token', 'oauth_token');
$app->get('/callbacks/:host', 'oauth_callback');

function oauth_login()
{
    global $app;
    echo 'Unimplemented!';
}

function oauth_token()
{
    global $app;
    echo 'Unimplemented!';
}

function oauth_callback($host)
{
    global $app;
    $auth = AuthProvider::getInstance();
    $provider = $auth->getSuplementalProviderByHost($host);
    if($provider === false)
    {
        $app->notFound();
        return; 
    }
    $res = $provider->authenticate($app->request->get(), $currentUser);
    switch($res)
    {
        case \Auth\Authenticator::SUCCESS:
            $app->redirect('/');
            break;
        default:
        case \Auth\Authenticator::LOGIN_FAILED:
            $app->redirect('/login.php?failed=1');
            break;
        case \Auth\Authenticator::ALREADY_PRESENT:
            $app->redirect('/user_exists.php?src='.$host.'&uid='.$currentUser->getUID());
            break;
    }
}

$app->run();
?>
