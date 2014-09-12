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
$page = new ProfilesPage('Burning Flipside Profiles Login');
$script_start_tag = $page->create_open_tag('script', array('src'=>'js/login.js'));
$script_close_tag = $page->create_close_tag('script');
$page->add_head_tag($script_start_tag.$script_close_tag);

//Add Jquery validator
$script_start_tag = $page->create_open_tag('script', array('src'=>'/js/jquery.validate.js'));
$page->add_head_tag($script_start_tag.$script_close_tag);

if(isset($_GET['return']))
{
    $return = '<input type="hidden" name="return" value="'.$_GET['return'].'"/>';
}
else
{
    $return = '';
}

$page->body = '
<div id="content" class="container">
    <div class="login-container">
    <h3>Burning Flipside Profile Login</h3>
    <form id="login_main_form" role="form">
        <input class="form-control" type="text" name="username" placeholder="Username or Email" required autofocus/>
        <input class="form-control" type="password" name="password" placeholder="Password" required/>
        '.$return.'
        <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
    </form>
    </div>
</div>';

$page->print_page();
// vim: set tabstop=4 shiftwidth=4 expandtab:
?>


