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
    echo json_encode(array('error' => "Not logged in"));
    die();
}

if(strtoupper($_SERVER['REQUEST_METHOD']) == 'GET')
{
    $sessions = FlipSession::get_all_sessions();
    $data = array();
    $ids = array_keys($sessions);
    for($i = 0; $i < count($sessions); $i++)
    {
        $data[$i] = array();
        $sess = $sessions[$ids[$i]];
        $data[$i][0] = $ids[$i];
        if(isset($sess['flipside_user']))
        {
            $data[$i][1] = $sess['flipside_user']->uid;
            if(is_array($data[$i][1]))
            {
                $data[$i][1] = $data[$i][1][0];
            }
        }
        else
        {
            $data[$i][1] = 'Anonymous';
        }
        if(isset($sess['ip_address']))
        {
            $data[$i][2] = $sess['ip_address'];
        }
        else
        {
            $data[$i][2] = 'N/A - Old Session';
        }
        if(isset($sess['init_time']))
        {
            $data[$i][3] = $sess['init_time'];
        }
        else
        {
            $data[$i][3] = 'N/A - Old Session';
        }
        if(session_id() == $ids[$i])
        {
            $data[$i][4] = 1;
        }
        else
        {
            $data[$i][4] = 0;
        }
    }
    echo json_encode(array('data'=>$data));
}
else if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    if(!isset($_POST['action']))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected action to be set"));
        die();
    }
    if(!isset($_POST['sids']) || !is_array($_POST['sids']))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected sids to be set as an array"));
        die();
    }
    $res = array();
    $overall = TRUE;
    for($i = 0; $i < count($_POST['sids']); $i++)
    {
        $temp = FALSE;
        $mg = '';
        switch($_POST['action'])
        {
            case 'delete':
                $temp = FlipSession::delete_session_by_id($_POST['sids'][$i]);
                if($temp == FALSE)
                {
                    $msg = 'Failed to delete file';
                }
                break;
            default:
                $msg = 'Unknown action '.$_POST['action'];
                break;
        }
        array_push($res, array('sid'=>$_POST['sids'][$i], 'res'=>$temp, 'msg'=>$msg));
        if($temp == FALSE)
        {
            $overall = FALSE;
        }
    }
    echo json_encode(array('overall'=>$overall, 'individual'=>$res));
}
else
{
    echo json_encode(array('error' => "Unrecognized Operation ".$_SERVER['REQUEST_METHOD']));
    die();
}
?>
