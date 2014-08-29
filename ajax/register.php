<?php
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once('class.FlipsideCAPTCHA.php');
require_once("class.FlipSession.php");
require_once("class.FlipsideLDAPServer.php");
require_once("class.FlipsideMail.php");
$user = FlipSession::get_user();
if($user != FALSE)
{
    echo json_encode(array('error' => "Already Logged In!"));
    die();
}

if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    if(!isset($_POST["email"]))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected email to be set"));
        die();
    }
    if(!isset($_POST["uid"]))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected uid to be set"));
        die();
    }
    if(!isset($_POST["password"]))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected password to be set"));
        die();
    }
    if(!isset($_POST["captcha"]))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected captcha to be set"));
        die();
    }
    /*Verify that the captcha is correct...*/
    $captcha = FlipSession::get_var('captcha');
    if(!$captcha->is_answer_right($_POST['captcha']))
    {
        echo json_encode(array('error' => 'Invalid Submission. Incorrect answer to CAPTCHA!'));
        die();
    }
    try
    {
        $server = new FlipsideLDAPServer();
        $user = new FlipsideUser($server, FALSE, $_POST['uid'], $_POST['password'], $_POST['email']);
        if($user->exists())
        {
            echo json_encode(array('error' => 'Invalid Submission. User already exists!'));
            die();
        }
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
            echo json_encode(array('success' => 0));
        }
        else
        {
            echo json_encode(array('error' => 'Internal Error! Failed to send mail. '. $mail->ErrorInfo));
        }
    }
    catch(Exception $e)
    {
        echo json_encode(array('error' => 'Invalid Submission. '.$e->getMessage()));
        die();
    }
}
else
{
    echo json_encode(array('error' => "Unrecognized Operation ".$_SERVER['REQUEST_METHOD']));
    die();
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
