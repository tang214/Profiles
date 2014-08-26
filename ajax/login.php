<?php
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipSession.php");
require_once("class.FlipsideLDAPServer.php");
$user = FlipSession::get_user();
if($user != FALSE)
{
    echo json_encode(array('error' => "Already Logged In!"));
    die();
}

if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    if(!isset($_POST["username"]))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected username to be set"));
        die();
    }
    if(!isset($_POST["password"]))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected password to be set"));
        die();
    }
    $server = new FlipsideLDAPServer();
    $user = $server->doLogin($_POST["username"], $_POST["password"]);
    if(!$user)
    {
        echo json_encode(array('error' => "Invalid Username or Password!"));
        die();
    }
    FlipSession::set_user($user);
    $return = '';
    if(isset($_POST["return"]))
    {
        $return = $_POST["return"];
    }
    else
    {
        $return = 'https://profiles.burningflipside.com';
    }
    echo json_encode(array('success' => 0, 'return' => $return)); 
}
else
{
    echo json_encode(array('error' => "Unrecognized Operation ".$_SERVER['REQUEST_METHOD']));
    die();
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
