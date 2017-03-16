<?php

function leads()
{
    global $app;
    $app->get('(/)', 'listPositions');
    $app->post('(/)', 'addPosition');
    $app->get('/:name(/)', 'getPosition');
    $app->patch('/:name(/)', 'updatePosition');
}

function getPositionsByType($type, $auth)
{
    switch($type)
    {
        case 'aar':
            $aarGroup = $auth->getGroupByName('AAR');
            return $aarGroup->members(true, false);
        case 'af':
            $afGroup = $auth->getGroupByName('AFs');
            return $afGroup->members(true, false);
        case 'cc':
            $ccGroup = $auth->getGroupByName('CC');
            return $ccGroup->members(true, false);
        case 'lead':
            $leadGroup = $auth->getGroupByName('Leads');
            return $leadGroup->members(true, false);
        default:
            $filter = new \Data\Filter('ou eq '.$type);
            return $auth->getUsersByFilter($filter);
    }
}

function getPositionsWithParams($params)
{
    $auth = AuthProvider::getInstance();
    if(isset($params['type']))
    {
        return getPositionsByType($params['type'], $auth);
    }
    $leads = array();
    $leadGroup = $auth->getGroupByName('Leads');
    $aarGroup  = $auth->getGroupByName('AAR');
    $afGroup   = $auth->getGroupByName('AFs');
    $ccGroup   = $auth->getGroupByName('CC');
    $leads     = array_merge($leads, $leadGroup->members(true, false));
    $leads     = array_merge($leads, $aarGroup->members(true, false));
    $leads     = array_merge($leads, $afGroup->members(true, false));
    $leads     = array_merge($leads, $ccGroup->members(true, false));
    return $leads;
}

function hasPositionAccess($app)
{
    return ($app->user->isInGroupNamed('Leads') || $app->user->isInGroupNamed('CC') || $app->user->isInGroupNamed('AFs'));
}

function listPositions()
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    if(!hasPositionAccess($app))
    {
        throw new Exception('Must be Lead', ACCESS_DENIED);
    }
    $params = $app->request->params();
    $leads = getPositionsWithParams($params);
    if($app->odata->select !== false)
    {
        $leads = $app->odata->filterArrayPerSelect($leads);
    }
    echo json_encode($leads);
}

function addPosition()
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    if(!$app->user->isInGroupNamed('LDAPAdmins'))
    {
        throw new Exception('Must be LDAPAdmins', ACCESS_DENIED);
    }
    $body = $app->request->getBody();
    $obj  = json_decode($body);
    $data_set = DataSetFactory::getDataSetByName('profiles');
    $data_table = $data_set['position'];
    $ret = $data_table->create($obj);
    echo json_encode($ret);
}

function getPosition($name)
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    if(!$app->user->isInGroupNamed('LDAPAdmins'))
    {
        throw new Exception('Must be LDAPAdmins', ACCESS_DENIED);
    }
    $data_set = DataSetFactory::getDataSetByName('profiles');
    $data_table = $data_set['position'];
    $position = $data_table->read(new \Data\Filter("short_name eq '$name'"), $app->odata->select, $app->odata->top, $app->odata->skip, $app->odata->orderby);
    if($position === false)
    {
        $app->notFound();
    }
    echo json_encode($position);
}

function updatePosition($name)
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    if(!$app->user->isInGroupNamed('LDAPAdmins'))
    {
        throw new Exception('Must be LDAPAdmins', ACCESS_DENIED);
    }
    $body = $app->request->getBody();
    $obj  = json_decode($body);
    $data_set = DataSetFactory::getDataSetByName('profiles');
    $data_table = $data_set['position'];
    $ret = $data_table->update(new \Data\Filter("short_name eq $name"), $obj);
    echo json_encode($ret);
}
?>
