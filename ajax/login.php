<?php
require_once("class.FlipsideLDAPServer.php");
require_once("class.FlipJax.php");
class LoginAjax extends FlipJaxSecure
{
    protected $post_params = array('username' => 'string', 'password' => 'string');

    function post($params)
    {
        if($this->is_logged_in())
        {
            return self::ALREADY_LOGGED_IN;
        }
        $server = new FlipsideLDAPServer();
        $user = $server->doLogin($_POST["username"], $_POST["password"]);
        if(!$user)
        {
            return self::INVALID_LOGIN;
        }
        FlipSession::set_user($user);
        $return = '';
        if(isset($params["return"]))
        {
            $return = $params["return"];
        }
        else
        {
            $return = 'https://profiles.burningflipside.com';
        }
        return array('return' => $return);
    }
}

$ajax = new LoginAjax();
$ajax->run();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
