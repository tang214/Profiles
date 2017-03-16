<?php

function sessions()
{
    global $app;
    $app->get('(/)', 'get_sessions');
    $app->delete('/:id(/)', 'end_session');
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
    $sessions = FlipSession::getAllSessions();
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
    $ret = FlipSession::deleteSessionById($id);
    echo json_encode($ret);
}

?>
