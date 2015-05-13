<?php
require_once('class.FlipREST.php');
require_once('class.FlipsideLDAPServer.php');

if($_SERVER['REQUEST_URI'][0] == '/' && $_SERVER['REQUEST_URI'][1] == '/')
{
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 1);
}

$app = new FlipREST();
$app->group('/users', 'users');
$app->group('/groups', 'groups');

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

function encode_user(&$user)
{
    $out = array();
    $out['displayName'] = get_single_value_from_array($user->displayName);
    $out['givenName']   = get_single_value_from_array($user->givenName);
    $out['jpegPhoto']   = base64_encode(get_single_value_from_array($user->jpegPhoto));
    $out['mail']        = get_single_value_from_array($user->mail);
    $out['mobile'] = get_single_value_from_array($user->mobile);
    $out['uid'] = get_single_value_from_array($user->uid);
    $out['o'] = get_single_value_from_array($user->o);
    $out['title'] = get_single_value_from_array($user->title);
    $out['st'] = get_single_value_from_array($user->st);
    $out['l'] = get_single_value_from_array($user->l);
    $out['sn'] = get_single_value_from_array($user->sn);
    $out['cn'] = get_single_value_from_array($user->cn);
    $out['postalAddress'] = get_single_value_from_array($user->postalAddress);
    $out['postalCode'] = get_single_value_from_array($user->postalCode);
    $out['c'] = get_single_value_from_array($user->c);
    $out['ou'] = get_single_value_from_array($user->ou);
    return $out;
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

function list_users()
{
    global $app;
    if(!$app->user)
    {
        $headers = apache_request_headers();
        if(isset($headers['apikey']))
        {
            require_once('/var/www/secure_settings/class.FlipsideSettings.php');
            if(!in_array($headers['apikey'], FlipsideSettings::$apikey['profiles']))
            {
                throw new Exception('Invalid API Key', ACCESS_DENIED);
            }
        }
        else
        {
            $app->response->setStatus(401);
            return;
        }
    }
    if($app->user && !$app->user->isInGroupNamed("LDAPAdmins"))
    {
        //Only return this user. This user doesn't have access to other accounts
        echo json_encode(array(encode_user($app->user)));
    }
    else
    {
        $params = $app->request->params();
        $server = new FlipsideLDAPServer();
        $users  = array();
        $filter = '(cn=*)';
        if(isset($params['filter']))
        {
             $filter = odata_filter_to_ldap_filter($params['filter'], $server);
        }
        $users  = $server->getUsers($filter);
        if($users === false)
        {
            echo json_encode(array());
            return;
        }
        $ret    = array();
        $start  = 0;
        $count  = count($users);
        if(isset($params['count']) && $params['count'] < $count)
        {
            $count = $params['count'];
        }
        if(isset($params['start']))
        {
            $start = $params['start'];
        }
        for($i = $start; $i < $start+$count; $i++)
        {
            array_push($ret, encode_user($users[$i]));
        }
        echo json_encode($ret);
    }
}

function show_user($uid = 'me')
{
    global $app;
    if(!$app->user)
    {
        $app->response->setStatus(401);
        return;
    }
    if($uid === 'me')
    {
        echo json_encode(encode_user($app->user));
    }
    else if($uid === $app->user->uid[0])
    {
        echo json_encode(encode_user($app->user));
    }
    else if($app->user->isInGroupNamed("LDAPAdmins"))
    {
        $server = new FlipsideLDAPServer();
        $users  = $server->getUsers('(uid='.$uid.')');
        if($users === FALSE || !isset($users[0])) $app->halt(404);
        echo json_encode(encode_user($users[0]));
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
        $server = new FlipsideLDAPServer();
        $groups = $server->getGroups();
        $ret    = array();
        $count  = count($groups);
        for($i = 0; $i < $count; $i++)
        {
            array_push($ret, encode_group($groups[$i]));
        }
        echo json_encode($ret);
    }
    else
    {
        list_groups_for_user();
    }
}

function list_groups_for_user($uid = 'me')
{
    global $app;
    if(!$app->user)
    {
        $app->response->setStatus(401);
        return;
    }
    $groups = FALSE;
    if($uid === 'me')
    {
        $groups = $app->user->getGroups();
    }
    if($groups === FALSE)
    {
        echo json_encode(array());
    }
    else
    {
        $count = count($groups);
        for($i = 0; $i < $count; $i++)
        {
            $groups[$i] = encode_group($groups[$i]);
        }
        echo json_encode($groups);
    }
}

function users()
{
    global $app;
    $app->get('', 'list_users');
    $app->get('/me', 'show_user');
    $app->get('/:uid', 'show_user');
    $app->get('/:uid/groups', 'list_groups_for_user');
}

function groups()
{
    global $app;
    $app->get('', 'list_groups');
}

$app->run();
?>
