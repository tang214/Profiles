<?php
require_once('class.FlipREST.php');
require_once('class.AuthProvider.php');

if($_SERVER['REQUEST_URI'][0] == '/' && $_SERVER['REQUEST_URI'][1] == '/')
{
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 1);
}

require('login.php');
require('users.php');
require('pending_users.php');
require('sessions.php');

$app = new FlipREST();
$app->post('/login', 'login');
$app->post('/logout', 'logout');
$app->group('/users', 'users');
$app->group('/groups', 'groups');
$app->group('/zip', 'postalcode');
$app->group('/pending_users', 'pending_users');
$app->group('/sessions', 'sessions');
$app->get('/leads', 'leads');
$app->get('/areas', 'get_areas');
$app->patch('/areas/:name', 'update_area');
$app->post('/areas', 'create_area');

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
            case 'lead':
                $leadGroup = $auth->get_group_by_name(false, 'Leads');
                $leads     = array_merge($leads, $leadGroup->members(true));
                break;
            default:
                $filter    = new \Data\Filter('ou eq '.$params['type']);
                $leads     = $auth->get_users_by_filter(false, $filter);
                break;
        }
    }
    echo json_encode($leads);
}

function get_areas()
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    $data_set = DataSetFactory::get_data_set('profiles');
    $data_table = $data_set['area'];
    $areas = $data_table->read($app->odata->filter, $app->odata->select, $app->odata->top, $app->odata->skip=false, $app->odata->orderby);
    echo json_encode($areas);
}

function update_area($name)
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
    $body = $app->request->getBody();
    $obj  = json_decode($body);
    $data_set = DataSetFactory::get_data_set('profiles');
    $data_table = $data_set['area'];
    $ret = $data_table->update(new \Data\Filter("short_code eq $name"), $obj); 
    echo json_encode($ret);
}

function create_area()
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
    $body = $app->request->getBody();
    $obj  = json_decode($body);
    $data_set = DataSetFactory::get_data_set('profiles');
    $data_table = $data_set['area'];
    $ret = $data_table->create($obj);
    echo json_encode($ret);
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
