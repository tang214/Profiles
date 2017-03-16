<?php

require_once('class.Singleton.php');
require_once('class.Settings.php');
$settings = \Settings::getInstance();

// array holding allowed Origin domains
$allowedOrigins = array(
  $settings->getGlobalSetting('www_url', 'https://www.burningflipside.com'),
  $settings->getGlobalSetting('wiki_url', 'https://wiki.burningflipside.com'),
  $settings->getGlobalSetting('profiles_url', 'https://profiles.burningflipside.com'),
  $settings->getGlobalSetting('secure_url', 'https://secure.burningflipside.com')
);

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
  foreach ($allowedOrigins as $allowedOrigin) {
    if (preg_match('#' . $allowedOrigin . '#', $_SERVER['HTTP_ORIGIN'])) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization,Cookie,apikey');
        break;
    }
  }
}

require_once('class.FlipREST.php');
require_once('class.AuthProvider.php');

if($_SERVER['REQUEST_URI'][0] == '/' && $_SERVER['REQUEST_URI'][1] == '/')
{
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 1);
}

require('areas.php');
require('aws.php');
require('groups.php');
require('leads.php');
require('login.php');
require('pending_users.php');
require('sessions.php');
require('users.php');

$app = new FlipREST();
$app->group('/areas(/)', 'areas');
$app->group('/aws(/)', 'aws');
$app->group('/groups(/)', 'groups');
$app->group('/leads(/)', 'leads');
$app->post('/login(/)', 'login');
$app->post('/logout(/)', 'logout');
$app->group('/pending_users(/)', 'pending_users');
$app->group('/sessions(/)', 'sessions');
$app->group('/users(/)', 'users');
$app->post('/zip(/)', 'validate_postal_code');


function hasUser($app)
{
    return ($app->user || $app->isLocal);
}

function isAdmin($app)
{
    return ($app->isLocal || $app->user->isInGroupNamed('LDAPAdmins'));
}

function validate_postal_code()
{
    global $app;
    $obj = $app->request->params();
    if($obj === null || count($obj) === 0)
    {
        $body = $app->request->getBody();
        $obj  = json_decode($body);
        $array = array('c' => $obj->c, 'postalCode'=>$obj->postalCode);
        $obj = $array;
    }
    if($obj['c'] == 'US')
    {
        if(preg_match("/^([0-9]{5})(-[0-9]{4})?$/i", $obj['postalCode']))
        {
            $contents = file_get_contents('http://ziptasticapi.com/'.$obj['postalCode']);
            $resp = json_decode($contents);
            if(isset($resp->error))
            {
                json_encode($resp->error);
            }
            else
            {
                json_encode(true);
            }
        }
        else
        {
            json_encode('Invalid Zip Code!');
        }
    }
    else
    {
        json_encode(true);
    }
}

$app->run();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
