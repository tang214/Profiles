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
require_once("class.FlipsideLDAPServer.php");
require_once("class.FlipSession.php");
require_once("class.FlipsideMail.php");
$server = new FlipsideLDAPServer();
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    /*This is a post*/
    if(!isset($_POST['email']) || !isset($_POST['uid']) || !isset($_POST['password']) || !isset($_POST['captcha']))
    {
        echo json_encode('Invalid Submission. Please fillout all fields!');
    }
    else
    {
         /*Verify that the captcha is correct...*/
         $captcha = FlipSession::get_var('captcha');
         if(!$captcha->is_answer_right($_POST['captcha']))
         {
             echo json_encode(array('status'=>-1,'msg'=>'Invalid Submission. Incorrect answer to CAPTCHA!'));
         }
         else
         {
             try
             {
                 $user = new FlipsideUser($server, FALSE, $_POST['uid'], $_POST['password'], $_POST['email']);
                 if($user->exists())
                 {
                     echo json_encode(array('status'=>-1,'msg'=>'Invalid Submission. User already exists!'));
                 }
                 else
                 {
                     $hash = $user->flushToTempDB();
                     $mail = new FlipsideMail();
                     $mail_data = array(
                         'to'       => $_POST['email'],
                         'subject'  => 'Burning Flipside Registration',
                         'body'     => 'Thank you for signing up with Burning Flipside. Your registration is not complete until you follow the link below.<br/>
                                        <a href="https://profiles.burningflipside.com/finish.php?hash='.$hash.'">Complete Registration</a><br/>
                                        Thank you,<br/>
                                        Burning Flipside Technology Team',
                         'alt_body' => 'Thank you for signing up with Burning Flipside. Your registration is not complete until you goto the address below.
                                        https://profiles.burningflipside.com/finish.php?hash='.$hash.'
                                        Thank you,
                                        Burning Flipside Technology Team'
                     );
                     if($mail->send_HTML($mail_data))
                     {
                         echo json_encode(array('status'=>0,'msg'=>'success'));
                     }
                     else
                     {
                         echo json_encode(array('status'=>-1,'msg'=>'failed to send mail'));
                     }
                 }
             }
             catch(Exception $e)
             {
                 echo json_encode(array('status'=>-1,'msg'=>'Invalid Submission. '.$e->getMessage()));
             }
         }
    }
}
else
{
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
            <tr><td>Password:</td><td><input type="password" name="password" id="password"/></td><td><label for="password" title=""/></td></tr>
            <tr><td>Confirm Password:</td><td><input type="password" name="password2"/></td><td><label class="error" for="password2" style="color:red"/></td></tr>
            <tr><td colspan="3">'.$captcha->draw_captcha(true, true).'</td></tr>
            '.$return.'
            <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Register"/></td></tr>
        </table>
    </form>
</div>';

$page->print_page();
}
// vim: set tabstop=4 shiftwidth=4 expandtab:
?>


