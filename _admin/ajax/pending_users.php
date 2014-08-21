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

function get_single_value_from_array($array)
{
    if(!is_array($array))
    {
        return $array;
    }
    if(isset($array[0]))
    {
        return $array[0];
    }
    else
    {
        return '';
    }
}

function userToArray($user)
{
    $res = array();
    array_push($res, get_single_value_from_array($user->uid));
    array_push($res, get_single_value_from_array($user->givenName).' '.get_single_value_from_array($user->sn));
    array_push($res, get_single_value_from_array($user->mail));
    return $res;
}

$users = FlipsideUser::get_all_temp_users();
$data = array();
if($users != FALSE)
{
    for($i = 0; $i < count($users); $i++)
    {
        $user_data = userToArray($users[$i]);
        array_push($data, $user_data);
    }
}

echo json_encode(array('data'=>$data));
?>
