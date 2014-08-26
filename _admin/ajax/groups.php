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

if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    echo json_encode($_POST);
}
else if(strtoupper($_SERVER['REQUEST_METHOD']) == 'GET')
{
    $server = new FlipsideLDAPServer();
    if(!isset($_GET['gid']))
    {
        $groups = $server->getGroups();
        $data = array();
        for($i = 0; $i < count($groups); $i++)
        {
            $group_data = groupToArray($groups[$i]);
            array_push($data, $group_data);
        }

        echo json_encode(array('data'=>$data));
    }
    else
    {
        $groups = $server->getGroups("(cn=".$_GET['gid'].")");
        if($groups == FALSE || !isset($groups[0]))
        {
            echo json_encode(array('error' => "Group not found!"));
            die();
        }
        $group = $groups[0];
        $group->cn = get_single_value_from_array($group->cn);
        $group->description = get_single_value_from_array($group->description);
        echo json_encode($group);
    }
}
else
{
    echo json_encode(array('error' => "Unrecognized Operation ".$_SERVER['REQUEST_METHOD']));
    die();
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
