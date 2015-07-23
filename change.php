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
require_once("class.FlipsideMail.php");
$page = new ProfilesPage('Burning Flipside Password Change');
$auth = AuthProvider::getInstance();
$require_current_pass = true;
$user = FlipSession::get_user();
if($user === false || $user === null)
{
    //We might be reseting a user's forgotten password...
    if(isset($_GET['hash']))
    {
        $user = $auth->get_user_by_reset_hash(false, $_GET['hash']);
        $require_current_pass = false;
    }
}

if($user === false || $user === null)
{
    if(isset($_GET['hash']))
    {
        $page->add_notification('This reset hash is no longer valid. Please select the neweset reset link in your email', FlipPage::NOTIFICATION_FAILED);
    }
    else
    {
        $page->add_notification('Please Log in first!', FlipPage::NOTIFICATION_FAILED);
    }
}
else
{
    $page->add_js_from_src('js/zxcvbn-async.js');
    $page->add_js_from_src('js/change.js');
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
$page->print_page();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>


