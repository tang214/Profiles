<?php
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipSession.php");
if(!FlipSession::is_logged_in())
{
    die("Not logged in!");
}

function get_single_value_from_array($array)
{
    if(isset($array[0]))
    {
        return $array[0];
    }
    else
    {
        return '';
    }
}

$user_copy = FlipSession::get_user_copy();
//Strip out password
$user_copy->userPassword = null;
//Flatten some arrays
$user_copy->displayName = get_single_value_from_array($user_copy->displayName);
$user_copy->givenName = get_single_value_from_array($user_copy->givenName);
$user_copy->jpegPhoto = base64_encode(get_single_value_from_array($user_copy->jpegPhoto));
$user_copy->mail = get_single_value_from_array($user_copy->mail);
$user_copy->mobile = get_single_value_from_array($user_copy->mobile);
$user_copy->uid = get_single_value_from_array($user_copy->uid);
$user_copy->title = get_single_value_from_array($user_copy->title);
$user_copy->st = get_single_value_from_array($user_copy->st);
$user_copy->l = get_single_value_from_array($user_copy->l);
$user_copy->sn = get_single_value_from_array($user_copy->sn);
$user_copy->cn = get_single_value_from_array($user_copy->cn);
echo json_encode($user_copy);
?>
