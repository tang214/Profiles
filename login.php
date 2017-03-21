<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Redirect users to https
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
    exit();
}
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles Login');
if($page->user !== false && $page->user !== null)
{
    if(isset($_GET['return']))
    {
        header('Location: '.$_GET['return']);
    }
    else
    {
        header('Location: /index.php');
    }
}

if(isset($_GET['return']))
{
    $return = '<input type="hidden" name="return" value="'.$_GET['return'].'"/>';
}
else
{
    $return = '';
}
if(isset($_GET['failed']))
{
    $page->addNotification('Login Failed! <a href="'.$page->resetUrl.'" class="alert-link">Click here to reset your password.</a>', $page::NOTIFICATION_FAILED);
}

$auth = \AuthProvider::getInstance();
$auth_links = $auth->getSupplementaryLinks();
$auth_links_str = '';
$count = count($auth_links);
for($i = 0; $i < $count; $i++)
{
    $auth_links_str .= $auth_links[$i];
}

$page->body .= '
<div id="content" class="container">
    <div class="login-container">
    <h3>Burning Flipside Profile Login</h3>
    <form id="login_main_form" role="form">
        <input class="form-control" type="text" name="username" placeholder="Username or Email" required autofocus/>
        <input class="form-control" type="password" name="password" placeholder="Password" required/>
        '.$return.'
        <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
    </form>
    '.$auth_links_str.'
    </div>
</div>';

$page->printPage();
// vim: set tabstop=4 shiftwidth=4 expandtab:
?>
