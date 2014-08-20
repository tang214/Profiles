<?php
if(strtoupper($_SERVER['REQUEST_METHOD']) != 'POST')
{
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
//Redirect users to https
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipsideLDAPServer.php");
require_once('class.ProfilesPage.php');
require_once("class.FlipsideMail.php");
$require_current_pass = true;
$user = FlipSession::get_user(TRUE);
if($user == FALSE)
{
    //We might be reseting a user's forgotten password...
    if(isset($_GET['hash']))
    {
        $user = FlipsideUser::getUserByResetHash($_GET['hash']);
        $require_current_pass = false;
    }
    else if(isset($_POST['hash']))
    {
         $user = FlipsideUser::getUserByResetHash($_POST['hash']);
         $require_current_pass = false;
    }
}
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    if($user == FALSE)
    {
        echo json_encode(array('status'=>-1,'msg'=>'Please Log in first!'));
        die();
    }
    /*This is a post*/
    if(isset($_POST['current']))
    {
        $server = new FlipsideLDAPServer();
        if($server->testLogin($user->uid[0],$_POST['current']) == FALSE)
        {
            echo json_encode(array('status'=>-1,'msg'=>'Current password incorrect!'));
            die();
        }
    }
    if(!isset($_POST['password']) || !isset($_POST['password2']))
    {
        echo json_encode(array('status'=>-1,'msg'=>'Invalid Submission. Please fillout all fields!'));
    }
    if($_POST['password'] != $_POST['password2'])
    {
        echo json_encode(array('status'=>-1,'msg'=>'Passwords must match!'));
    }
    $user->setPassword($_POST['password']);
    $mail = new FlipsideMail();
    $forward = '';
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
        $forward = 'Behind Proxy: '.$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    $mail_data = array(
            'to'       => $user->mail[0],
            'subject'  => 'Burning Flipside Password Change',
            'body'     => 'Someone (quiet possibly you) has changed your Flipside password.<br/>
                           If you did not request this change please notify the technology team (technology@burningflipside.com).<br/>
                           IP Address: '.$_SERVER['REMOTE_ADDR'].'<br/>
                           '.$forward.'<br/>
                           Thank you,<br/>
                           Burning Flipside Technology Team',
            'alt_body' => 'Someone (quiet possibly you) has changed your Flipside password.
                           If you did not request this change please notify the technology team (technology@burningflipside.com).
                           IP Address: '.$_SERVER['REMOTE_ADDR'].'
                           '.$forward.'
                           Thank you,<br/>
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
else
{
    if($user == FALSE)
    {
        die("Please Log in first!");
    }
    $page = new ProfilesPage('Burning Flipside Password Change');
    $script_start_tag = $page->create_open_tag('script', array('src'=>'js/jquery.validate.js'));
    $script_close_tag = $page->create_close_tag('script');
    $page->add_head_tag($script_start_tag.$script_close_tag);

    $script_start_tag = $page->create_open_tag('script', array('src'=>'js/zxcvbn-async.js'));
    $page->add_head_tag($script_start_tag.$script_close_tag);

    $script_start_tag = $page->create_open_tag('script', array('src'=>'js/change.js'));
    $page->add_head_tag($script_start_tag.$script_close_tag);

    $current ='';
    if($require_current_pass)
    {
        $current = '<tr><td>Current Password:</td><td><input type="password" name="current" required/></td></tr>';
    }
    else
    {
        $current = '<input type="hidden" name="hash" value="'.$_GET['hash'].'"/>';
    }

    $page->body = '
        <div id="content">
        <h3>Burning Flipside Password Change</h3>
        <form action="/change.php" method="post" name="form" id="form">
        <table>
        '.$current.'
        <tr><td>Password:</td><td><input type="password" name="password" id="password"/></td><td><label for="password" title=""/></td></tr>
        <tr><td>Confirm Password:</td><td><input type="password" name="password2"/></td><td><label class="error" for="password2" style="color:red"/></td></tr>
        <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="Change"/></td></tr>
        </table>
        </form>
        </div>';

    $page->print_page();
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>


