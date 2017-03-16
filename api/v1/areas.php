<?php

function areas()
{
    global $app;
    $app->get('(/)', 'list_areas');
    $app->post('(/)', 'create_area');
    $app->get('/:name(/)', 'get_area');
    $app->patch('/:name(/)', 'update_area');
    $app->get('/:name/leads(/)', 'get_area_leads');
}

function list_areas()
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    $data_set = DataSetFactory::getDataSetByName('profiles');
    $data_table = $data_set['area'];
    $areas = $data_table->read($app->odata->filter, $app->odata->select, $app->odata->top, $app->odata->skip, $app->odata->orderby);
    echo json_encode($areas);
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
    $data_set = DataSetFactory::getDataSetByName('profiles');
    $data_table = $data_set['area'];
    $ret = $data_table->create($obj);
    echo json_encode($ret);
}

function get_area($name)
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    $data_set = DataSetFactory::getDataSetByName('profiles');
    $data_table = $data_set['area'];
    $areas = $data_table->read(new \Data\Filter("short_name eq '$name'"), $app->odata->select, $app->odata->top, $app->odata->skip, $app->odata->orderby);
    if($areas === false)
    {
        $app->notFound();
    }
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
    $data_set = DataSetFactory::getDataSetByName('profiles');
    $data_table = $data_set['area'];
    $ret = $data_table->update(new \Data\Filter("short_name eq $name"), $obj);
    echo json_encode($ret);
}

function get_area_leads($name)
{
    global $app;
    if(!$app->user)
    {
        throw new Exception('Must be logged in', ACCESS_DENIED);
    }
    $data_set = DataSetFactory::getDataSetByName('profiles');
    $data_table = $data_set['position'];
    $leads = $data_table->read(new \Data\Filter("area eq '$name'"), $app->odata->select, $app->odata->top, $app->odata->skip, $app->odata->orderby);
    if($leads === false)
    {
        $app->notFound();
    }
    echo json_encode($leads);
}

/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
