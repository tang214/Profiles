<?php
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

require_once("class.FlipsideLDAPServer.php");
require_once("class.FlipJax.php");
class UserAjax extends FlipJaxSecure
{
    function validate_user_can_read_uid($uid)
    {
        if($this->user_in_group("LDAPAdmins"))
        {
            return self::SUCCESS;
        }
        $my_uid = $this->user->uid[0];
        if($my_uid == FALSE)
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Failed to obtain UID for user!");
        }
        if($my_uid != $uid)
        {
            return array('err_code' => self::ACCESS_DENIED, 'reason' => "User must be a member of LDAPAdmins to access someone else's user!");
        }
        return self::SUCCESS;
    }

    function get($params)
    {
        $user_copy = FALSE;
        if(!isset($params['uid']))
        {
            $user_copy = FlipSession::get_user();
        }
        else
        {
            $uid = $params['uid'];
            $res = $this->validate_user_can_read_uid($uid);
            if($res != self::SUCCESS)
            {
                return $res;
            }
            $server = new FlipsideLDAPServer();
            $users = $server->getUsers("(uid=".$uid.")");
            if($users == FALSE || !isset($users[0]))
            {
                die('User not found!');
            }

            $user_copy = $users[0];
            unset($users);
        }
        //Strip out password
        $user_copy->userPassword = null;
        //Flatten some arrays
        $out = array();
        $out['displayName'] = get_single_value_from_array($user_copy->displayName);
        $out['givenName'] = get_single_value_from_array($user_copy->givenName);
        $out['jpegPhoto'] = base64_encode(get_single_value_from_array($user_copy->jpegPhoto));
        $out['mail'] = get_single_value_from_array($user_copy->mail);
        $out['mobile'] = get_single_value_from_array($user_copy->mobile);
        $out['uid'] = get_single_value_from_array($user_copy->uid);
        $out['o'] = get_single_value_from_array($user_copy->o);
        $out['title'] = get_single_value_from_array($user_copy->title);
        $out['st'] = get_single_value_from_array($user_copy->st);
        $out['l'] = get_single_value_from_array($user_copy->l);
        $out['sn'] = get_single_value_from_array($user_copy->sn);
        $out['cn'] = get_single_value_from_array($user_copy->cn);
        $out['postalAddress'] = get_single_value_from_array($user_copy->postalAddress);
        $out['postalCode'] = get_single_value_from_array($user_copy->postalCode);
        $out['c'] = get_single_value_from_array($user_copy->c);
        $out['ou'] = get_single_value_from_array($user_copy->ou);
        $out['title'] = get_single_value_from_array($user_copy->title);
        $out['dn'] = $user_copy->dn;
        unset($user_copy);
        return $out;
    }

    function do_post_rename($new_uid, $old_uid)
    {
        return array('err_code' => self::INVALID_PARAM, 'action_name' => 'rename');
    }

    function do_post_delete($uid)
    {
        $server = new FlipsideLDAPServer();
        $user_copy = FALSE;
        if($uid != null)
        {
            $res = $this->validate_user_can_read_uid($uid);
            if($res != self::SUCCESS)
            {
                return $res;
            }
            $users = $server->getUsers("(uid=".$uid.")");
            if($users == FALSE || !isset($users[0]))
            {
                return array('err_code' => self::INTERNAL_ERROR, 'reason' => "User not found!");
            }
            $user_copy = $users[0];
        }
        else
        {
            $user_copy = FlipSession::get_user();
        }
        if($server->delete_dn($user_copy->dn))
        {
            return self::SUCCESS;
        }
        return array('err_code' => self::INTERNAL_ERROR, 'reason' => $server->lastError());
    }

    function do_post_action($params)
    {
        switch($params['action'])
        {
            case 'rename':
                return $this->do_post_rename($params['uid'], $params['old_uid']);
            case 'delete':
                if(!isset($params['uid']))
                {
                    $params['uid'] = null;
                }
                return $this->do_post_delete($params['uid']);
            default:
                return array('err_code' => self::INVALID_PARAM, 'action_name' => $params['action']);
        }
    }

    function do_post_user_edit($params)
    {
        $uid = $params['uid'];
        unset($params['uid']);
        if(!isset($params['old_uid']))
        {
            //Assume it's the same...
            $params['old_uid'] = $uid;
        }
        if($params['old_uid'] != $uid)
        {
            return $this->post_rename($uid, $params['old_uid']);
        }
        else
        {
            unset($params['old_uid']);
        }
        $res = $this->validate_user_can_read_uid($uid);
        if($res != self::SUCCESS)
        {
            return $res;
        }
        $user_copy = $this->user;
        if($this->user->uid[0] != $uid)
        {
            $server = new FlipsideLDAPServer();
            $users = $server->getUsers("(uid=".$uid.")");
            if($users == FALSE || !isset($users[0]))
            {
                return array('err_code' => self::INTERNAL_ERROR, 'reason' => "User not found!");
            }
            $user_copy = $users[0]; 
        }
        $change = array();
        $valid_params = array('givenName', 'sn', 'displayName', 'mail', 'mobile', 'postalAddress', 'postalCode', 'l', 'st', 'jpegPhoto', 'c');
        if($this->user_in_group("LDAPAdmins"))
        {
            array_push($valid_params, 'ou');
            array_push($valid_params, 'title');
        }
        for($i = 0; $i < count($valid_params); $i++)
        {
            if(isset($params[$valid_params[$i]]))
            {
                if(strlen($params[$valid_params[$i]]) > 0)
                {
                    $change[$valid_params[$i]] = $params[$valid_params[$i]];
                }
                unset($params[$valid_params[$i]]);
            }
        }
        $valid_transforms = array('street' => 'postalAddress', 'zip' => 'postalCode');
        foreach($values as $from => $to)
        {
            if(isset($params[$from]))
            {
                if(strlen($params[$from]) > 0)
                {
                    $change[$to] = $params[$from];
                }
                unset($params[$from]);
            }
        }
        if($user_copy->setAttribs($change))
        {
            FlipSession::refresh_user();
            return array('changes'=>$change, 'unset'=>$params);
        }
        else
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Failed to set user properties!");
        }
    }

    function post($params)
    {
        if(!$this->is_logged_in())
        {
            return array('err_code' => self::ACCESS_DENIED, 'reason' => "Not Logged In!");
        }
        if(isset($params['action']))
        {
            return $this->do_post_action($params);
        }
        else
        {
            $res = $this->validate_params($params, array('uid'=>'string'));
            if($res == self::SUCCESS)
            {
                $res = $this->do_post_user_edit($params);
            }
            return $res;
        }
    }
}

$ajax = new UserAjax();
$ajax->run();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
