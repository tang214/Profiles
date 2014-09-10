<?php
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipSession.php");
$user = FlipSession::get_user(TRUE);
if($user == FALSE || !$user->isInGroupNamed("LDAPAdmins"))
{
    die("Not logged in!");
}
require_once("class.FlipsideCAPTCHA.php");
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'GET')
{
    $captchas = FlipsideCAPTCHA::get_all();
    if(isset($_GET['cid']))
    {
        for($i = 0; $i < count($captchas); $i++)
        {
            if($captchas[$i]->random_id == $_GET['cid'])
            {
                echo json_encode($captchas[$i]->jsonSerialize());
                break;
            }
        }
    }
    else
    {
        for($i = 0; $i < count($captchas); $i++)
        {
            $captchas[$i] = $captchas[$i]->jsonSerialize();
        }
        echo json_encode($captchas);
    }
}
else if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    if(!isset($_POST['id']))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected id to be set"));
        die();
    }
    if(!isset($_POST['question']))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected question to be set"));
        die();
    }
    else if(strlen($_POST['question']) < 1)
    {
        echo json_encode(array('error' => "Invalid Parameter! question must be a non-zero length string"));
        die();
    }
    if(!isset($_POST['answer']))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected answer to be set"));
        die();
    }
    else if(strlen($_POST['answer']) < 1)
    {
        echo json_encode(array('error' => "Invalid Parameter! answer must be a non-zero length string"));
        die();
    }
    if(!isset($_POST['hint']))
    {
        $_POST['hint'] = '';
    }
    if($_POST['id'] == 'NEW')
    {
        $new_id = FlipsideCAPTCHA::save_new_captcha($_POST['question'], $_POST['hint'], $_POST['answer']);
        echo json_encode(array('success' => 0, 'id' => $new_id));
    }
    else
    {
        echo json_encode($_POST);
    }
}
else
{
    echo json_encode(array('error' => "Unrecognized Operation ".$_SERVER['REQUEST_METHOD']));
    die();
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
