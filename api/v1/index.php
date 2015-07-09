<?php
require_once('class.FlipREST.php');
require_once('class.AuthProvider.php');

if($_SERVER['REQUEST_URI'][0] == '/' && $_SERVER['REQUEST_URI'][1] == '/')
{
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 1);
}

require('login.php');
require('users.php');

$app = new FlipREST();
$app->post('/login', 'login');
$app->post('/logout', 'logout');
$app->group('/users', 'users');
$app->group('/groups', 'groups');
$app->group('/zip', 'postalcode');
$app->group('/pending_users', 'pending_users');
$app->get('/leads', 'leads');
$app->get('/sessions', 'get_sessions');
$app->delete('/sessions/:id', 'end_session');

function odata_filter_to_ldap_filter($filter, $server)
{
    if(strstr($filter, ' and ') !== false)
    {
        $ret = '(&';
        $toks = explode(' and ', $filter);
        $count = count($toks);
        for($i = 0; $i < $count; $i++)
        {
            $ret.= odata_filter_to_ldap_filter($toks[$i], $server);
        }
        return $ret.')';
    }
    if(strstr($filter, ' or ') != false)
    {
    
        throw new Exception('Don\'t support compound filters yet!');
    }
    //filter is <fieldname> <operator> <data>
    $field = $server->ldap_escape(strtok($filter, ' '));
    $operator = strtok(' ');
    $rest = $server->ldap_escape(str_replace("'", "", strtok("\0")));
    $negate = false;
    switch($operator)
    {
        case 'ne':
            $negate = true;
        case 'eq':
            $operator = '=';
            break;
        case 'lt':
            $operator = '<';
            break;
        case 'le':
            $operator = '<=';
            break;
        case 'gt':
            $operator = '>';
            break;
        case 'ge':
            $operator = '>=';
            break;
    }
    $ret = '';
    if($negate)
    {
        $ret.='(!';
    }
    if($rest === 'null' && $operator === '=')
    {
        if($negate)
        {
            return '('.$field.'=*)';
        }
        else
        {
            return '(!('.$field.'=*))';
        }
    }
    $ret.='('.$field.$operator.$rest.')';
    if($negate)
    {
        $ret.=')';
    }
    return $ret;
}

function redirect_non_logged_in($app)
{
    if(!$app->user)
    {
        $app->response->redirect('/OAUTH2/authorize.php', 303);
        return true;
    }
    return false;
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

function dn_to_uri($dn)
{
    global $app;
    $comps = explode(',', $dn);
    $dn = $comps[0];
    $path = $app->request->getRootUri();
    if(strncmp($dn, 'uid=', 4) == 0)
    {
        $uid = substr($dn, 4);
        return $path.'/users/'.$uid;
    }
    else if(strncmp($dn, 'cn=', 3) == 0)
    {
        return $path.'/groups/'.substr($dn, 3);
    }
    else
    {
        return $path.'/users/'.$dn;
    }
}

function encode_group(&$group)
{
    $out = array();
    $out['member']      = array();
    for($i = 0; $i < $group->member['count']; $i++)
    {
        $out['member'][$i] = dn_to_uri($group->member[$i]);
    }
    $out['cn']          = get_single_value_from_array($group->cn);
    $out['description'] = get_single_value_from_array($group->description);
    return $out;
}

function list_pending_users()
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    if(!$app->user->isInGroupNamed("LDAPAdmins"))
    {
        throw new Exception('Must be Admin', ACCESS_DENIED);
    }
    $auth = AuthProvider::getInstance();
    $users = $auth->get_pending_users_by_filter(false, $app->odata->filter, $app->odata->select, $app->odata->top, $app->odata->skip, $app->odata->orderby);
    echo json_encode($users);
}

function show_pending_user($hash)
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    if(!$app->user->isInGroupNamed("LDAPAdmins"))
    {
        throw new Exception('Must be Admin', ACCESS_DENIED);
    }
    else
    {
        $user = \AuthProvider::getInstance()->get_pending_users_by_filter(false, new \Data\Filter("hash eq $hash"));
    }
    if($user === false) $app->halt(404);
    if(!is_object($user) && isset($user[0]))
    {
        $user = $user[0];
    }
    echo $user->serializeObject();
}

function delete_pending_user($hash)
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    if(!$app->user->isInGroupNamed("LDAPAdmins"))
    {
        throw new Exception('Must be Admin', ACCESS_DENIED);
    }
    else
    {
        $res = \AuthProvider::getInstance()->delete_pending_users_by_filter(false, new \Data\Filter("hash eq $hash"));
        echo json_encode($res);
    }
}

function activate_user($hash)
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    else
    {
        $auth = \AuthProvider::getInstance();
        $user = $auth->get_pending_users_by_filter(false, new \Data\Filter("hash eq $hash"));
        $res = $auth->active_user(false, $user);
        echo json_encode($res);
    }
}

function list_groups()
{
    global $app;
    if(!$app->user)
    {
        $app->response->setStatus(401);
        return;
    }
    if($app->user->isInGroupNamed("LDAPAdmins"))
    {
        $auth = AuthProvider::getInstance();
        $users = $auth->get_groups_by_filter(false, $app->odata->filter, $app->odata->select, $app->odata->top, $app->odata->skip, $app->odata->orderby);
        echo json_encode($users);
    }
    else
    {
        list_groups_for_user();
    }
}

function validate_post_code()
{
    global $app;
    $obj = $app->request->params();
    if($obj === null || count($obj) === 0)
    {
        $body = $app->request->getBody();
        $obj  = json_decode($body);
        $array = array('c' => $obj->c, 'postalCode'=>$obj->postalCode);
        $obj = $array;
    }
    if($obj['c'] == 'US')
    {
        if(preg_match("/^([0-9]{5})(-[0-9]{4})?$/i",$obj['postalCode']))
        {
            $contents = file_get_contents('http://ziptasticapi.com/'.$obj['postalCode']);
            $resp = json_decode($contents);
            if(isset($resp->error))
            {
                json_encode($resp->error);
            }
            else
            {
                json_encode(true);
            }
        }
        else
        {
            json_encode('Invalid Zip Code!');
        }
    }
    else
    {
        json_encode(true);
    }
}

function leads()
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    if(!$app->user->isInGroupNamed("Leads") && !$app->user->isInGroupNamed("CC"))
    {
        throw new Exception('Must be Lead', ACCESS_DENIED);
    }
    $params = $app->request->params();
    $auth = AuthProvider::getInstance();
    $leads     = array();
    if(!isset($params['type']))
    {
        $leadGroup = $auth->get_group_by_name(false, 'Leads');
        $aarGroup  = $auth->get_group_by_name(false, 'AAR');
        $afGroup   = $auth->get_group_by_name(false, 'AFs');
        $ccGroup   = $auth->get_group_by_name(false, 'CC');
        $leads     = array_merge($leads, $leadGroup->members(true));
        $leads     = array_merge($leads, $aarGroup->members(true));
        $leads     = array_merge($leads, $afGroup->members(true));
        $leads     = array_merge($leads, $ccGroup->members(true));
    }
    else
    {
        switch($params['type'])
        {
            case 'aar':
                $aarGroup  = $auth->get_group_by_name(false, 'AAR');
                $leads     = array_merge($leads, $aarGroup->members(true));
                break;
            case 'af':
                $afGroup   = $auth->get_group_by_name(false, 'AFs');
                $leads     = array_merge($leads, $afGroup->members(true));
                break;
            case 'cc':
                $ccGroup   = $auth->get_group_by_name(false, 'CC');
                $leads     = array_merge($leads, $ccGroup->members(true));
                break;
            default:
                $filter    = new \Data\Filter('ou eq '.$params['type']);
                $leads     = $auth->get_users_by_filter(false, $filter);
                break;
        }
    }
    echo json_encode($leads);
}

function get_sessions()
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    if(!$app->user->isInGroupNamed("LDAPAdmins"))
    {
        throw new Exception('Must be Admin', ACCESS_DENIED);
    }
    $sessions = FlipSession::get_all_sessions();
    if($sessions !== false)
    {
        $count = count($sessions);
        $sid = session_id();
        for($i = 0; $i < $count; $i++)
        {
            if(strcasecmp($sessions[$i]['sid'], $sid) === 0)
            {
                $sessions[$i]['current'] = true;
            }
        }
    }
    echo json_encode($sessions);
}

function end_session($id)
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    if(!$app->user->isInGroupNamed("LDAPAdmins"))
    {
        throw new Exception('Must be Admin', ACCESS_DENIED);
    }
    $ret = FlipSession::delete_session_by_id($id);
    echo json_encode($ret);
}

function pending_users()
{
    global $app;
    $app->get('', 'list_pending_users');
    $app->get('/:uid', 'show_pending_user');
    $app->delete('/:uid', 'delete_pending_user');
    $app->post('/:uid/Actions/activate', 'activate_user');
}

function groups()
{
    global $app;
    $app->get('', 'list_groups');
}

function postalcode()
{
    global $app;
    $app->post('', 'validate_post_code');
}

$app->run();
?>
