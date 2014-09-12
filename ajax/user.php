<?php
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipSession.php");
$user = FlipSession::get_user(TRUE);
if($user == FALSE)
{
    echo json_encode(array('error' => "Not Logged In!"));
    die();
}
$is_admin = $user->isInGroupNamed("LDAPAdmins");

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

$uid = '';
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    if(!isset($_POST['uid']) || !is_string($_POST['uid']))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected uid as a string"));
        die();
    }
    $uid = $_POST['uid'];
    unset($_POST['uid']);
    if(!isset($_POST['old_uid']))
    {
        //Assume it's the same...
        $_POST['old_uid'] = $uid;
    }
    if($_POST['old_uid'] != $uid)
    {
        echo json_encode(array('error' => "Not Implemented! Haven't added uid change support yet!"));
        die();
    }
    else
    {
        unset($_POST['old_uid']);
    }
    $server = new FlipsideLDAPServer();
    $users = $server->getUsers("(uid=".$uid.")");
    if($users == FALSE || !isset($users[0]))
    {
        echo json_encode(array('error' => "User not found!"));
        die();
    }
    $user_copy = $users[0];
    $change = array();
    if(isset($_POST['givenName']))
    {
        if(strlen($_POST['givenName']) > 0)
        {
            $change['givenName'] = $_POST['givenName'];
        }
        unset($_POST['givenName']);
    }
    if(isset($_POST['sn']))
    {
        if(strlen($_POST['sn']) > 0)
        {
            $change['sn'] = $_POST['sn'];
        }
        unset($_POST['sn']);
    }
    if(isset($_POST['displayName']))
    {
        if(strlen($_POST['displayName']) > 0)
        {
            $change['displayName'] = $_POST['displayName'];
        }
        unset($_POST['displayName']);
    }
    if(isset($_POST['mail']) && strlen($_POST['mail']) > 0)
    {
        $change['mail'] = $_POST['mail'];
        unset($_POST['mail']);
    }
    if(isset($_POST['mobile']))
    {
        if(strlen($_POST['mobile']) > 0)
        {
            $change['mobile'] = $_POST['mobile'];
        }
        unset($_POST['mobile']);
    }
    if(isset($_POST['postalAddress']))
    {
        if(strlen($_POST['postalAddress']) > 0)
        {
            $change['postalAddress'] = $_POST['postalAddress'];
        }
        unset($_POST['postalAddress']);
    }
    else if(isset($_POST['street']))
    {
        if(strlen($_POST['street']) > 0)
        {
            $change['postalAddress'] = $_POST['street'];
        }
        unset($_POST['street']);
    }
    if(isset($_POST['postalCode']))
    {
        if(strlen($_POST['postalCode']) > 0)
        {
            $change['postalCode'] = $_POST['postalCode'];
        }
        unset($_POST['postalCode']);
    }
    else if(isset($_POST['zip']))
    {
        if(strlen($_POST['zip']) > 0)
        {
            $change['postalCode'] = $_POST['zip'];
        }
        unset($_POST['zip']);
    }
    if(isset($_POST['l']))
    {
        if(strlen($_POST['l']) > 0)
        {
            $change['l'] = $_POST['l'];
        }
        unset($_POST['l']);
    }
    if(isset($_POST['st']))
    {
        if(strlen($_POST['st']) > 0)
        {
            $change['st'] = $_POST['st'];
        }
        unset($_POST['st']);
    }
    if(isset($_POST['jpegPhoto']))
    {
        if(strlen($_POST['jpegPhoto']) > 0)
        {
            $change['jpegPhoto'] = base64_decode($_POST['jpegPhoto']);
        }
        unset($_POST['jpegPhoto']);
    }
    if($user_copy->setAttribs($change))
    {
        echo json_encode(array('success' => 0, 'changes'=>$change, 'unset'=>$_POST));
    }
    else
    {
        echo json_encode(array('error' => "Failed to set prop!"));
    }
}
else if(strtoupper($_SERVER['REQUEST_METHOD']) == 'GET')
{
    if(!isset($_GET['uid']))
    {
        $user_copy = $user;
    }
    else
    {
        $uid = $_GET['uid'];
        if(!$is_admin && $uid != $user->uid[0])
        {
            echo json_encode(array('error' => "Unauthorized Access!"));
            die();
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
    $out['dn'] = $user_copy->dn;
    unset($user_copy);
    echo json_encode($out);
}
else
{
    echo json_encode(array('error' => "Unrecognized Operation ".$_SERVER['REQUEST_METHOD']));
    die();
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
