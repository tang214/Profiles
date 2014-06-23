<?php
require_once("class.FlipsideLDAPServer.php");
require_once("class.FlipSession.php");
$server = new FlipsideLDAPServer();
$email = trim(strtolower($_GET['email']));
if(!$server->userWithEmailExists($email))
{
    echo "true";
}
else
{
    echo 'false';
}
?>
