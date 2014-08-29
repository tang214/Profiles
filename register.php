<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Redirect users to https
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once('class.FlipsideCAPTCHA.php');
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles Registration');
$script_start_tag = $page->create_open_tag('script', array('src'=>'js/jquery.validate.js'));
$script_close_tag = $page->create_close_tag('script');
$page->add_head_tag($script_start_tag.$script_close_tag);

$script_start_tag = $page->create_open_tag('script', array('src'=>'js/zxcvbn-async.js'));
$page->add_head_tag($script_start_tag.$script_close_tag);

$script_start_tag = $page->create_open_tag('script', array('src'=>'js/register.js'));
$page->add_head_tag($script_start_tag.$script_close_tag);

$captcha = new FlipsideCAPTCHA();
FlipSession::set_var('captcha', $captcha);

if(isset($_GET['return']))
{
    $return = '<input type="hidden" name="return" value="'.$_GET['return'].'"/>';
}
else
{
    $return = '';
}

$page->body = '
<div id="content">
    <h3>Burning Flipside Profile Registration</h3>
    <form action="register.php" method="post" name="form" id="form">
        <table>
            <tr><td>Email:</td><td><input type="text" name="email" id="email"/></td><td><label class="error" for="email" style="color:red"/></td></tr>
            <tr><td>Username:</td><td><input type="text" name="uid" id="uid"/></td><td><label class="error" for="uid" style="color:red"/></td></tr>
            <tr><td>Password:</td><td><input type="password" name="password" id="password" class="pass"/></td><td><label class="error" for="password" title=""/></td></tr>
            <tr><td>Confirm Password:</td><td><input type="password" name="password2"/></td><td><label class="error" for="password2" style="color:red"/></td></tr>
            <tr><td colspan="3">'.$captcha->draw_captcha(true, true).'</td></tr>
            '.$return.'
            <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Register"/></td></tr>
        </table>
    </form>
</div>';

$page->print_page();
// vim: set tabstop=4 shiftwidth=4 expandtab:
?>


