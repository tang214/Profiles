<?php
class SessionsAPI extends Http\Rest\RestAPI
{
    public function setup($app)
    {
        $app->get('[/]', array($this, 'getSessions'));
        $app->delete('/{id}', array($this, 'endSession'));
    }

    protected function validateIsAdmin($request)
    {
        $user = $request->getAttribute('user');
        if($user === false)
        {
            throw new Exception('Must be logged in', \Http\Rest\ACCESS_DENIED);
        }
        if(!$user->isInGroupNamed('LDAPAdmins'))
        {
            throw new Exception('Must be Admin', \Http\Rest\ACCESS_DENIED);
        }
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
