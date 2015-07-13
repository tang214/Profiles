<?php

function pending_users()
{
    global $app;
    $app->get('', 'list_pending_users');
    $app->get('/:uid', 'show_pending_user');
    $app->delete('/:uid', 'delete_pending_user');
    $app->get('/:uid/Actions/activate', 'activate_user');
    $app->post('/:uid/Actions/activate', 'activate_user');
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
        $user = \AuthProvider::getInstance()->get_pending_users_by_filter(false, new \Data\Filter("hash eq '$hash'"));
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
        $res = \AuthProvider::getInstance()->delete_pending_users_by_filter(false, new \Data\Filter("hash eq '$hash'"));
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
        $user = $auth->get_pending_users_by_filter(false, new \Data\Filter("hash eq '$hash'"));
        if($user === false || !isset($user[0])) $app->halt(404);
        $res = $auth->activate_pending_user(false, $user[0]);
        if($app->request->isGet())
        {
            if($res)
            {
            	$app->redirect('../../');
            }
            else
            {
                $app->redirect('../../activate_error.php');
            }
        }
        else
        {
            echo json_encode($res);
        }
    }
}
?>
