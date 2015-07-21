<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Redirect users to https
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipSession.php");
require_once('class.ProfilesPage.php');
require_once('class.FlipsideMail.php');
$page = new ProfilesPage('Burning Flipside Profiles Reset');
$page->add_js(JS_BOOTBOX);
$page->add_js_from_src('js/reset.js');

if($page->user !== false && $page->user !== null)
{
    //User is logged in. They can reset their password...
    $page->body = '
        <div id="content">
            <h3>Burning Flipside Password Reset</h3>
            <div class="form-group">
                <label for="oldpass" class="col-sm-2 control-label">Current Password:</label>
                <div class="col-sm-10">
                    <input class="form-control" type="password" name="oldpass" id="oldpass" required/>
                </div>
            </div>
            <div class="clearfix visible-sm visible-md visible-lg"></div>
            <div class="form-group">
                <label for="newpass" class="col-sm-2 control-label">New Password:</label>
                <div class="col-sm-10">
                    <input class="form-control" type="password" name="newpass" id="newpass" required/>
                </div>
            </div>
            <div class="clearfix visible-sm visible-md visible-lg"></div>
            <div class="form-group">
                <label for="confirm" class="col-sm-2 control-label">Confirm Password:</label>
                <div class="col-sm-10">
                    <input class="form-control" type="password" name="confirm" id="confirm" required/>
                </div>
            </div>
            <div class="clearfix visible-sm visible-md visible-lg"></div>
            <button name="submit" class="btn btn-primary" onclick="change_password();">Change Password</button>
        </div>
    ';
}
else
{
    $page->body = '
        <div id="content">
            <h3>Burning Flipside Login Reset/Recover</h3>
            <div class="radio">
                <label>
                    <input type="radio" name="forgot" value="user"/>
                    Forgot Username
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="forgot" value="pass"/>
                    Forgot Password
                </label>
            </div>
            <div class="clearfix visible-sm visible-md visible-lg"></div>
            <button name="submit" class="btn btn-primary" onclick="what_did_they_forget();">Next</button>
        </div>';
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    if(isset($_POST['email']))
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
                               If you did not request this reminder, don\'t worry. This email was sent only to you.<br/>
                               If you receive many of these requests, you can notify the technology team (technology@burningflipside.com).<br/>
                               Thank you,<br/>
                               Burning Flipside Technology Team',
                'alt_body' => 'Someone (quite possibly you) has requested a reminder of your Flipside username.
                               Your Flipside username is '.$users[0]->uid[0].'
                               If you did not request this reminder, don\'t worry. This email was sent only to you.
                               If you receive many of these requests, you can notify the technology team (technology@burningflipside.com).
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
                'body'     => 'Someone (quite possibly you) has requested a password reset of your Flipside account.<br/>
                               To reset your password click on the link below.<br/>
                               <a href="https://profiles.burningflipside.com/change.php?hash='.$hash.'">Reset Password</a><br/>
                               If you did not request this reset, don\'t worry. This email was sent only to you and your password has not been changed.<br/>
                               If you receive many of these requests, you can notify the technology team (technology@burningflipside.com).<br/>
                               Thank you,<br/>
                               Burning Flipside Technology Team',
                'alt_body' => 'Someone (quite possibly you) has requested a password reset of your Flipside account.
                               To reset your password copy the following URL into your browser.
                               https://profiles.burningflipside.com/change.php?hash='.$hash.'
                               If you did not request this reset, don\'t worry. This email was sent only to you and your password has not been changed.
                               If you receive many of these requests, you can notify the technology team (technology@burningflipside.com).
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
}

$page->print_page();
// vim: set tabstop=4 shiftwidth=4 expandtab:
?>


