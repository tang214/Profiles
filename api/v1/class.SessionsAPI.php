<?php
class SessionsAPI extends ProfilesAdminAPI
{
    public function setup($app)
    {
        $app->get('[/]', array($this, 'getSessions'));
        $app->delete('/{id}', array($this, 'endSession'));
    }

    public function getSessions($request, $response, $args)
    {
        $this->validateIsAdmin($request);
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
        return $response->withJson($sessions);
    }

    public function endSession($request, $response, $args)
    {
        $this->validateIsAdmin($request);
        $ret = FlipSession::deleteSessionById($args['id']);
        return $response->withJson($ret);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
