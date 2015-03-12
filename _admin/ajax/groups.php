<?php
require_once("class.FlipSession.php");
require_once("class.FlipJax.php");

class GroupAjax extends FlipJaxSecure
{
    function userIsAdmin()
    {
        if(!$this->user_in_group("LDAPAdmins"))
        {
            return array('err_code' => self::ACCESS_DENIED, 'reason' => "User must be a member of LDAPAdmins!");
        }
        return self::SUCCESS;
    }

    function getAllGroups()
    {
        $server = new FlipsideLDAPServer();
        $res = $this->userIsAdmin();
        if($res != self::SUCCESS)
        {
            return $res;
        }
        $groups = $server->getGroups();
        $data = array();
        for($i = 0; $i < count($groups); $i++)
        {
            $group_data = groupToArray($groups[$i]);
            array_push($data, $group_data);
        }
        return array('data'=>$data);
    }

    function getGroupData($gid, $fullMemberData = FALSE)
    {
        $res = $this->userIsAdmin();
        if($res != self::SUCCESS)
        {
            return $res;
        }
        $server = new FlipsideLDAPServer();
        $groups = $server->getGroups("(cn=".$_GET['gid'].")");
        if($groups == FALSE || !isset($groups[0]))
        {
            echo json_encode(array('error' => "Group not found!"));
            die();
        }
        $group = $groups[0];
        $group->cn = get_single_value_from_array($group->cn);
        $group->description = get_single_value_from_array($group->description);
        if($fullMemberData)
        {
            $members = array();
            for($i = 0; $i < $group->member['count']; $i++)
            {
                if(strncmp($group->member[$i], "uid=", 4) == 0)
                {
                    $user = $server->getUserByDN($group->member[$i]);
                    array_push($members, array('dn'=>$group->member[$i], 'username'=>$user->uid[0], 'email'=>$user->mail[0], 'name'=>$user->givenName[0].' '.$user->sn[0]));
                }
                else
                {
                    $child_group = $server->getGroupByDN($group->member[$i]);
                    array_push($members, array('dn'=>$group->member[$i], 'username'=>$child_group->cn[0], 'email'=>'N/A', 'name'=>$child_group->cn[0]));
                }
            }
            $group->member = $members;
        }
        return array('group'=>$group);
    }

    function getAllNonMembers($gid)
    {
        $res = $this->userIsAdmin();
        if($res != self::SUCCESS)
        {
            return $res;
        }
        $server = new FlipsideLDAPServer();
        $data = array();
        if($gid !== 'null')
        {
            $group_filter = '(&(cn=*)(!(cn='.$gid.'))';
            $user_filter = '(&(cn=*)';
            $groups = $server->getGroups("(cn=".$gid.")");
            if($groups == FALSE || !isset($groups[0]))
            {
                echo json_encode(array('error' => "Group not found!"));
                die();
            }
            $group = $groups[0];
            for($i = 0; $i < $group->member['count']; $i++)
            {
                $dn_comps = explode(',',$group->member[$i]);
                if(strncmp($group->member[$i], "uid=", 4) == 0)
                {
                    $user_filter.='(!('.$dn_comps[0].'))';
                }
                else
                {
                    $group_filter.='(!('.$dn_comps[0].'))';
                }
            }
            $user_filter.=')';
            $group_filter.=')';
        }
        else
        {
            $group_filter = '(cn=*)';
            $user_filter = '(cn=*)';
        }
        $groups = $server->getGroups($group_filter);
        for($i = 0; $i < count($groups); $i++)
        {
            array_push($data, array('dn'=>$groups[$i]->dn, 'username'=>$groups[$i]->cn[0], 'email'=>'N/A', 'name'=>$groups[$i]->cn[0]));
        }
        $users = $server->getUsers($user_filter);
        for($i = 0; $i < count($users); $i++)
        {
            array_push($data, array('dn'=>$users[$i]->dn, 'username'=>$users[$i]->uid[0], 'email'=>$users[$i]->mail[0], 'name'=>$users[$i]->givenName[0].' '.$users[$i]->sn[0]));
        } 
        return array('data'=>$data);
    }

    function get($params)
    {
        if(!isset($params['gid']))
        {
            return $this->getAllGroups();
        }
        else
        {
            if(isset($params['nonMembersOnly']))
            {
                return $this->getAllNonMembers($params['gid']);
            }
            else if(isset($params['fullMember']))
            {
                return $this->getGroupData($params['gid'], TRUE);
            }
            else
            {
                return $this->getGroupData($params['gid']);
            }
        }
    }

    function postNewGroup($params)
    {
        $res = $this->validate_params($params, array('members'=>'array'));
        if($res != self::SUCCESS)
        {
            return $res;
        }

        $server = new FlipsideLDAPServer();
        //Make sure gid is available
        $groups = $server->getGroups("(cn=".$gid.")");
        if($groups != FALSE && count($groups) > 0)
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Group Already Exists");
        }
        $desc = '';
        if(isset($params['description']))
        {
            $desc = $params['description'];
        }
        $group = FlipsideUserGroup::newGroup($params['gid'], $desc, $params['members']);
        if($server->writeObject($group))
        {
            return self::SUCCESS;
        }
        else
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Failed to create group!");
        }
    }

    function postEditGroup($params)
    {
        $res = $this->validate_params($params, array('members'=>'array'));
        if($res != self::SUCCESS)
        {
            return $res;
        }
        if(isset($params['old_gid']) && ($params['old_gid'] != $params['gid']))
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Not Implemented! Haven't added gid change support yet!");
        }
        else
        {
            unset($params['old_gid']);
        }
        $server = new FlipsideLDAPServer();
        //Make sure gid is available
        $groups = $server->getGroups("(cn=".$params['gid'].")");
        if($groups == FALSE || count($groups) == 0)
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Unable to locate group!");
        }
        $change = array();
        unset($params['gid']);
        if(isset($params['description']))
        {
            if(strlen($params['description']) > 0)
            {
                $change['description'] = $params['description'];
            }
            unset($params['description']);
        }
        populate_members($groups[0], $change, $params['members']);
        unset($params['members']);
        if($groups[0]->setAttribs($change))
        {
            return array('success' => 0, 'changes'=>$change, 'unset'=>$params);
        }
        else
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Failed to set property!");
        }
    }

    function post($params)
    {
        if(!$this->is_logged_in())
        {
            return array('err_code' => self::ACCESS_DENIED, 'reason' => "Not Logged In!");
        }
        $res = $this->userIsAdmin();
        if($res != self::SUCCESS)
        {
            return $res;
        }
        $res = $this->validate_params($params, array('action'=>'string','gid'=>'string'));
        if($res != self::SUCCESS)
        {
            return $res;
        }
        if(strpos($params['gid'], ' ') != FALSE || strpos($params['gid'], ',') != FALSE)
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Invalid Parameter! Invalid Group Name!");
        }
        switch($_POST['action'])
        {
            case 'new':
                unset($params['action']);
                $res = $this->postNewGroup($params);
                break;
            case 'edit':
                unset($params['action']);
                $res = $this->postEditGroup($params);
                break;
            default:
                $res = array('err_code' => self::INVALID_PARAM, 'action_name' => $params['action']);
                break;
        }
        return $res;
    }
}

$ajax = new GroupAjax();
$ajax->run();

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
    array_push($res, get_single_value_from_array($group->dn));
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
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
