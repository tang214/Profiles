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

function groupToArray($group)
{
    $res = array();
    array_push($res, get_single_value_from_array($group->cn));
    array_push($res, get_single_value_from_array($group->description));
    return $res;
}

$server = new FlipsideLDAPServer();
$groups = $server->getGroups();
$data = array();
for($i = 0; $i < count($groups); $i++)
{
    $group_data = groupToArray($groups[$i]);
    array_push($data, $group_data);
}

echo json_encode(array('data'=>$data));
?>
