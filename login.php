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
<div id="content">
    <h3>Burning Flipside Profile Login</h3>
    <form id="login_main_form">
        <table>
            <tr><td>Username or email:</td><td><input type="text" name="username"/></td></tr>
            <tr><td>Password:</td><td><input type="password" name="password"/></td></tr>'.$return.'
            <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Login"/></td></tr>
        </table>
    </form>
</div>';

$page->print_page();
// vim: set tabstop=4 shiftwidth=4 expandtab:
?>


