<?php
require_once('vendor/autoload.php');
require_once('class.AuthProvider.php');

if($_SERVER['REQUEST_URI'][0] == '/' && $_SERVER['REQUEST_URI'][1] == '/')
{
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 1);
}

$app = new \Slim\App();
$app->get('/callbacks/{host}', 'oauthCallback');

function oauthCallback($request, $response, $args)
{
    $host = $args['host'];
    $auth = AuthProvider::getInstance();
    $provider = $auth->getSuplementalProviderByHost($host);
    if($provider === false)
    {
        return $response->withStatus(404);
    }
    $res = $provider->authenticate($app->request->get(), $currentUser);
    switch($res)
    {
        case \Auth\Authenticator::SUCCESS:
            $response = $response->withHeader('Location', '/');
            break;
        default:
        case \Auth\Authenticator::LOGIN_FAILED:
            $response = $response->withHeader('Location', '/login.php?failed=1');
            break;
        case \Auth\Authenticator::ALREADY_PRESENT:
            $response = $response->withHeader('Location', '/user_exists.php?src='.$host.'&uid='.$currentUser->getUID());
            break;
    }
    return $response->withStatus(302);
}

$app->run();
?>
