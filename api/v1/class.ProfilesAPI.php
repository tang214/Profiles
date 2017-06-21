<?php
class ProfilesAPI extends Http\Rest\RestAPI
{
    public function setup($app)
    {
        $app->post('/login[/]', array($this, 'login'));
        $app->post('/logout[/]', array($this, 'logout'));
        $app->post('/zip[/]', array($this, 'validateZip'));
    }

    public function login($request, $response)
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
            $user = \FlipSession::getUser();
            $privateKey = file_get_contents('/var/www/secure_settings/jwtRS256.key');
            $groups = $user->getGroups();
            if($groups === false)
            {
                $groups = array();
            }
            $count = count($groups);
            for($i = 0; $i < $count; $i++)
            {
                $groups[$i] = $groups[$i]->getGroupName();
            }
            $token = array(
                'iss' => $request->getUri()->getHost(),
                'sub' => $user->uid,
                'private' => array('Flipside'=>array(
                    'email'=>$user->mail,
                    'groups'=>$groups,
                    'sessionIDs'=> array(
                        'php'=>session_id()
                        )
                    )      
                )
            );
            $cookieParams = session_get_cookie_params();
            $jwt = \Firebase\JWT\JWT::encode($token, $privateKey, 'RS256');
            $response = $response->withHeader('Set-Cookie', 'Flipside_JWT='.$jwt.'; path=/; domain='.$cookieParams['domain'].'; secure');
            return $response->withJson($res);
        }
    }

    public function logout($request, $response)
    {
        FlipSession::end();
        $cookieParams = session_get_cookie_params();
        $response = $response->withHeader('Set-Cookie', 'Flipside_JWT=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0; path=/; domain='.$cookieParams['domain'].'; secure');
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
        return $response->withJson($ret);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
