<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles');

if(!isset($_GET['hash']))
{
    $page->addNotification("No hash set! Please ensure you copy the link exactly from the email!", $page::NOTIFICATION_FAILED);
}
else
{
    $auth = AuthProvider::getInstance();
    $user = $auth->getTempUserByHash($_GET['hash']);
    if($user === false)
    {
        $page->addNotification("Unable to locate user! This registration has either expired or already been completed!", $page::NOTIFICATION_FAILED);
    }
    else
    {
        if($auth->activatePendingUser($user) === false)
        {
            $page->addNotification("Internal Error! ".$server->lastError(), $page::NOTIFICATION_FAILED);
        }
        else
        {
            $page->addNotification('You have successfully registered! You will be redirected to the login page in <span id="secs">5</span> seconds&hellip;', $page::NOTIFICATION_SUCCESS);
            $page->addJSByURI('js/finish.js');
        }
    }
}
$page->printPage();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
