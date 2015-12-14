<?php

function login()
{
    global $app;
    $auth = AuthProvider::getInstance();
    $res = $auth->login($app->request->params('username'), $app->request->params('password'));
    if($res === false)
    {
        $app->response->setStatus(403);
    }
    else
    {
        echo @json_encode($res);
    }
}

function logout()
{
    FlipSession::end();
    echo 'true';
}

?>
