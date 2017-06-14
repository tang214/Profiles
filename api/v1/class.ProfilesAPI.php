<?php
class ProfilesAPI extends Http\Rest\RestAPI
{
    public function setup($app)
    {
        $app->post('/login[/]', array($this, 'login'));
        $app->post('/logout[/]', array($this, 'logout'));
        $app->post('/zip[/]', array($this, 'validateZip'));
    }

    public function login($request, $response, $args)
    {
        $params = $request->getParams();
        if(!isset($params['username']) || !isset($params['password']))
        {
            return $response->withStatus(400);
        }
        $auth = AuthProvider::getInstance();
        $res = $auth->login($params['username'], $params['password']);
        if($res === false)
        {
            return $response->withStatus(403);
        }
        else
        {
            return $response->withJson($res);
        }
    }

    public function logout($request, $response, $args)
    {
        FlipSession::end();
        return $response->withJson(true);
    }

    public function validateZip($request, $response, $args)
    {
        $obj = $request->getQueryParams();
        if(empty($obj))
        {
            $obj = (array)$request->getParsedBody();
        }
        $ret = false;
        if($obj['c'] == 'US')
        {
            if(preg_match("/^([0-9]{5})(-[0-9]{4})?$/i", $obj['postalCode']))
            {
                $contents = file_get_contents('http://ziptasticapi.com/'.$obj['postalCode']);
                $resp = json_decode($contents);
                if(isset($resp->error))
                {
                    $ret = $resp->error;
                }
                else
                {
                    $ret = true;
                }
            }
            else
            {
                $ret = 'Invalid Zip Code!';
            }
        }
        else
        {
            $ret = true;
        }
        return $request->withJson($ret);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
