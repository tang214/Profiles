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

function get_rds_from_post_value($members, $server, &$error)
{
    $member_dns = array();
    for($i = 0; $i < count($members); $i++)
    {
        $rdn = FALSE;
        if(strncmp($members[$i], "group->", 7) == 0)
        {
            //Lookup group name
            $groups = $server->getGroups("(cn=".substr($members[$i], 7).")");
            if($groups != FALSE && isset($groups[0]))
            {
                $rdn = $groups[0]->dn;
            }
        }
        else
        {
            //Lookup user name
            $users = $server->getUsers("(uid=".substr($members[$i], 5).")");
            if($users != FALSE && isset($users[0]))
            {
                $rdn = $users[0]->dn;
            }
        }
        if($rdn == FALSE)
        {
            if(strncmp($members[$i], "group->", 7) == 0)
            {
                $error = "Failed to locate group ".substr($members[$i], 7);
            }
            else
            {
                $error = "Failed to locate user ".$members[$i];
            }
            return FALSE;
        }
        array_push($member_dns, $rdn);
    }
    return $member_dns;
}

function make_new_group($gid, $desc, $members, &$error)
{
    $server = new FlipsideLDAPServer();
    //Make sure gid is available
    $groups = $server->getGroups("(cn=".$gid.")");
    if($groups != FALSE && count($groups) > 0)
    {
        $error = "Group name already in use!";
        return FALSE;
    }
    //Convert members to rdns
    $member_dns = get_rds_from_post_value($members, $server, $error);
    if($member_dns == FALSE)
    {
        return FALSE;
    }
    $group = FlipsideUserGroup::newGroup($gid, $desc, $member_dns);
    return $server->writeObject($group);
}

function get_group_class($group)
{
    for($i = 0; $i < count($group->objectClass); $i++)
    {
        if(strcasecmp($group->objectClass[$i],"groupOfUniqueNames") == 0)
        {
            return "groupOfUniqueNames";
        }
        if(strcasecmp($group->objectClass[$i],"groupOfNames") == 0)
        {
            return "groupOfNames";
        }
        if(strcasecmp($group->objectClass[$i],"posixGroup") == 0)
        {
            return "posixGroup";
        }
    }
    return "unknown";
}

function members_to_uid_array($members)
{
    $res = array();
    for($i = 0; $i < count($members); $i++)
    {
        if(strncmp($members[$i], "uid=", 4) != 0)
        {
            if(strncmp($members[$i], "cn=", 3) != 0)
            {
                echo json_encode(array('error' => "Invalid Parameter! Cannot add a group to a posixGroup!"));
                die();
            }
            else
            {
                echo json_encode(array('error' => "Internal Error! Cannot process member ".$members[$i]));
                die();
            }
        }
        else
        {
            $member_bits = explode(',', $members[$i]);
            array_push($res, substr($member_bits[0], 4));
        }
    }
    return $res;
}

function populate_members($group, &$change, $members)
{
    $class = get_group_class($group);
    if($class == "unknown")
    {
        echo json_encode(array('error' => "Internal Error! Unknown Group Class! ".print_r($group->objectClass, TRUE)));
        die();
    }
    switch($class)
    {
        case "groupOfUniqueNames":
            $change['uniqueMember'] = $members;
            break;
        case "groupOfNames":
            $change['member'] = $members;
            break;
        case "posixGroup":
            $change['memberUID'] = members_to_uid_array($members);
            break;
        default:
            echo json_encode(array('error' => "Internal Error! Don't know how to handle class ".$class));
            die();
    }
}

function edit_existing_group($gid, $desc, $members, &$error)
{
    if(!isset($_POST['old_gid']))
    {
        //Assume it's the same...
        $_POST['old_gid'] = $gid;
    }
    if($_POST['old_gid'] != $gid)
    {
        $error = "Not Implemented! Haven't added gid change support yet!";
        return FALSE;
    }
    else
    {
        unset($_POST['old_gid']);
    }
    $server = new FlipsideLDAPServer();
    //Make sure gid is available
    $groups = $server->getGroups("(cn=".$gid.")");
    if($groups == FALSE || count($groups) == 0)
    {
        $error = "Group does not exist!";
        return FALSE;
    }
    //Convert members to rdns
    $member_dns = get_rds_from_post_value($members, $server, $error);
    if($member_dns == FALSE)
    {
        return FALSE;
    }
    $change = array();
    unset($_POST['gid']);
    unset($_POST['members']);
    if(isset($_POST['description']))
    {
        if(strlen($_POST['description']) > 0)
        {
            $change['description'] = $_POST['description'];
        }
        unset($_POST['description']);
    }
    populate_members($groups[0], $change, $member_dns);
    if($groups[0]->setAttribs($change))
    {
        echo json_encode(array('success' => 0, 'changes'=>$change, 'unset'=>$_POST));
        die();
    }
    else
    {
        $error = "Failed to set prop!";
        return FALSE;
    }
}

if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    if(!isset($_POST['action']))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected action to be set"));
        die();
    }
    if(!isset($_POST['gid']))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected gid to be set"));
        die();
    }
    else if(strpos($_POST['gid'], ' ') != FALSE || strpos($_POST['gid'], ',') != FALSE)
    {
        echo json_encode(array('error' => "Invalid Parameter! Invalid Group Name!", 'invalid' => 'gid'));
        die();
    }
    $error = FALSE;
    switch($_POST['action'])
    {
        case 'new':
            if(!isset($_POST['members']) || !is_array($_POST['members']))
            {
                echo json_encode(array('error' => "Invalid Parameter! A group requires at least one member", 'invalid' => 'members'));
                die();
            }
            $result = make_new_group($_POST['gid'], $_POST['description'], $_POST['members'], $error);
            if($result == FALSE && $error == FALSE)
            {
                $error = "Failed to create new group!";
            }
            break;
        case 'edit':
            if(!isset($_POST['members']) || !is_array($_POST['members']))
            {
                echo json_encode(array('error' => "Invalid Parameter! A group requires at least one member", 'invalid' => 'members'));
                die();
            }
            $result = edit_existing_group($_POST['gid'], $_POST['description'], $_POST['members'], $error);
            if($result == FALSE && $error == FALSE)
            {
                $error = "Failed to edit group!";
            }
            break;
        default:
            $error = 'Unknown action '.$_POST['action'];
            break;
    }
    if($error)
    {
        echo json_encode(array('error' => $error));
    }
    else
    {
        echo json_encode(array('success' => 0));
    }
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
