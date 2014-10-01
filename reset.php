<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Redirect users to https
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipsideLDAPServer.php");
require_once("class.FlipSession.php");
require_once('class.ProfilesPage.php');
require_once('class.FlipsideMail.php');
$page = new ProfilesPage('Burning Flipside Profiles Reset');
//Add Jquery validator
$script_start_tag = $page->create_open_tag('script', array('src'=>'js/jquery.validate.js'));
$script_close_tag = $page->create_close_tag('script');
$page->add_head_tag($script_start_tag.$script_close_tag);
//Add Reset Javascript
$script_start_tag = $page->create_open_tag('script', array('src'=>'js/reset.js'));
$page->add_head_tag($script_start_tag.$script_close_tag);
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    if(isset($_POST['forgot']))
    {
        if($_POST['forgot'] == 'user')
        {
            $page->body = '
        <div id="content">
            <h3>Burning Flipside Username Recovery</h3>
            <form action="/reset.php" method="post" name="user_form" id="user_form">
                <table>
                    <tr><td>Email:</td><td><input type="text" name="email"/></td></tr>
                    <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Next ->"/></td></tr>
                </table>
            </form>
        </div>';
        }
        else if($_POST['forgot'] == 'pass')
        {
            $page->body = '
        <div id="content">
            <h3>Burning Flipside Password Reset</h3>
            <form action="/reset.php" method="post" name="pass_form" id="pass_form">
                <table>
                    <tr><td>User ID:</td><td><input type="text" name="uid"/></td></tr>
                    <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Next ->"/></td></tr>
                </table>
            </form>
        </div>';

        }
        else
        {
            $page->body = 'Unknown POST!';
        }
    }
    else if(isset($_POST['email']))
    {
        //Email UID to email address if it exists
        $server = new FlipsideLDAPServer(); 
        $users = $server->getUsers("(mail=".$_POST['email'].")");
        if($users == FALSE || !isset($users[0]))
        {
            $page->body = '
        <div id="content">
            <h3>Burning Flipside Username Recovery</h3>
            Error: No such email address on record. You can register for a new account <a href="/register.php">here</a>.
        </div>';
        }
        else
        {
            $mail = new FlipsideMail();
            $mail_data = array(
                'to'       => $_POST['email'],
                'subject'  => 'Burning Flipside Username Recovery',
                'body'     => 'Someone (quite possibly you) has requested a reminder of your Flipside username.<br/>
                               Your Flipside username is '.$users[0]->uid[0].'<br/>
                               If you did not request this reminder don\'t worry. This email was sent only to you.<br/>
                               If you receive many of these requests you can notify the technology team (technology@burningflipside.com).<br/>
                               Thank you,<br/>
                               Burning Flipside Technology Team',
                'alt_body' => 'Someone (quite possibly you) has requested a reminder of your Flipside username.
                               Your Flipside username is '.$users[0]->uid[0].'
                               If you did not request this reminder don\'t worry. This email was sent only to you.
                               If you receive many of these requests you can notify the technology team (technology@burningflipside.com).
                               Thank you,
                               Burning Flipside Technology Team'
            );
            if($mail->send_HTML($mail_data))
            {
                //Add Redirect Javascript
                $script_start_tag = $page->create_open_tag('script', array('src'=>'js/redirect.js'));
                $page->add_head_tag($script_start_tag.$script_close_tag);
                $page->body = '
        <div id="content">
            <h3>Burning Flipside Username Recovery</h3>
            User ID located. You should recieve an email with your user ID shortly.
        </div>';
            }
            else
            {
                $page->body = 'Error: Unable to send email!';
            }
        }
    }
    else if(isset($_POST['uid']))
    {
        //Create temporary password reset link
        $server = new FlipsideLDAPServer();
        $users = $server->getUsers("(uid=".$_POST['uid'].")");
        if($users == FALSE || !isset($users[0]))
        {
            $page->body = '
        <div id="content">
            <h3>Burning Flipside Password Reset</h3>
            Error: No such user ID on record. You can register for a new account <a href="/register.php">here</a>.
        </div>';
        }
        else
        {
            $hash = $users[0]->putInResetDB();
            $mail = new FlipsideMail();
            $mail_data = array(
                'to'       => $users[0]->mail[0],
                'subject'  => 'Burning Flipside Password Reset',
                'body'     => 'Someone (quiet possibly you) has requested a password reset of your Flipside account.<br/>
                               To reset your password click on the link below.<br/>
                               <a href="https://profiles.burningflipside.com/change.php?hash='.$hash.'">Reset Password</a><br/>
                               If you did not request this reset don\'t worry. This email was sent only to you and your password has not been changed.<br/>
                               If you receive many of these requests you can notify the technology team (technology@burningflipside.com).<br/>
                               Thank you,<br/>
                               Burning Flipside Technology Team',
                'alt_body' => 'Someone (quiet possibly you) has requested a password reset of your Flipside account.
                               To reset your password copy the following URL into your browser.
                               https://profiles.burningflipside.com/change.php?hash='.$hash.'
                               If you did not request this reset don\'t worry. This email was sent only to you and your password has not been changed.
                               If you receive many of these requests you can notify the technology team (technology@burningflipside.com).
                               Thank you,
                               Burning Flipside Technology Team'
            );
            if($mail->send_HTML($mail_data))
            {
                //Add Redirect Javascript
                $script_start_tag = $page->create_open_tag('script', array('src'=>'js/redirect.js'));
                $page->add_head_tag($script_start_tag.$script_close_tag);
                $page->body = '
        <div id="content">
            <h3>Burning Flipside Password Reset</h3>
            User located. You should recieve an email with instructions to reset your password shortly.
        </div>';
            }
            else
            {
                $page->body = 'Error: Unable to send email!';
            }
        }
    }
    else
    {
        $page->body = 'Unknown POST!';
    }
}
else
{
    $page->body = '
        <div id="content">
            <h3>Burning Flipside Login Reset/Recover</h3>
            <form action="/reset.php" method="post" name="forgot_form" id="forgot_form">
                <table>
                    <tr>
                        <td><input type="radio" name="forgot" value="user" class="required">Forgot Username</input></td>
                        <td rowspan="2"><label class="error" for="forgot"></div></td>
                    </tr>
                    <tr><td><input type="radio" name="forgot" value="pass">Forgot Password</input></td></tr>
                    <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Next ->"/></td></tr>
                </table>
            </form>
        </div>';
}

$page->print_page();
// vim: set tabstop=4 shiftwidth=4 expandtab:
?>


