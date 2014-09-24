<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('class.ProfilesPage.php');
require_once("class.FlipsideLDAPServer.php");
require_once("class.FlipsideUser.php");
$page = new ProfilesPage('Burning Flipside Profiles');

if(!isset($_GET['hash']))
{
    $page->add_notification("No hash set! Please ensure you copy the link exactly from the email!", $page::NOTIFICATION_FAILED);
}
else
{
    $user = FlipsideUser::get_temp_user_by_hash($_GET['hash']);
    if($user == FALSE)
    {
        $page->add_notification("Unable to locate user! This registration has either expired or already been completed!", $page::NOTIFICATION_FAILED);
    }
    else
    {
        $server = new FlipsideLDAPServer();
        $user->resetServer($server);
        if($server->writeObject($user) == FALSE)
        {
            $page->add_notification("Internal Error! ".$server->lastError(), $page::NOTIFICATION_FAILED);
        }
        else
        {
            $page->add_notification('You have successfully registered! You will be redirect to the login page in <span id="secs">5</span> seconds&hellip;', $page::NOTIFICATION_SUCCESS);
            $user->eraseFromTempDB($_GET['hash']);
            $page->add_js_from_src('js/finish.js');
        }
    }
}
$page->print_page();
?>
