<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Redirect users to https
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Password Change');
$auth = AuthProvider::getInstance();
$require_current_pass = true;
$user = $page->user;
if($user === false || $user === null)
{
    //We might be reseting a user's forgotten password...
    if(isset($_GET['hash']))
    {
        $user = $auth->getUserByResetHash($_GET['hash']);
        $require_current_pass = false;
    }
}

if($user === false || $user === null)
{
    if(isset($_GET['hash']))
    {
        $page->addNotification('This reset hash is no longer valid. Please select the neweset reset link in your email', FlipPage::NOTIFICATION_FAILED);
    }
    else
    {
        $page->addNotification('Please Log in first!', FlipPage::NOTIFICATION_FAILED);
    }
}
else
{
    $page->addJSByURI('js/zxcvbn-async.js');
    $page->addJSByURI('js/change.js');
    $current ='';
    if($require_current_pass)
    {
        $current = '<div class="form-group"><input class="form-control" type="password" id="current" name="current" placeholder="Current Password" required autofocus/></div>';
    }
    else
    {
        $current = '<input type="hidden" id="hash" name="hash" value="'.$_GET['hash'].'"/>';
    }
    $page->body = '
        <div id="content" class="container">
            <h3>Burning Flipside Password Change</h3>
            <form name="form" id="form" role="form">
                '.$current.'
                <div class="form-group"><input class="form-control" type="password" id="password" name="password" placeholder="New Password" required/></div>
                <div class="form-group"><input class="form-control" type="password" id="password2" name="password2" placeholder="Confirm Password" required/></div>
                <button class="btn btn-lg btn-primary btn-block" type="submit">Change Password</button>
            </form>
        </div>';
}
$page->printPage();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>


