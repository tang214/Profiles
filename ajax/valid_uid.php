<?php
require_once("class.FlipsideLDAPServer.php");
require_once("class.FlipSession.php");
$server = new FlipsideLDAPServer();
$uid = trim(strtolower($_GET['uid']));
if(!$server->userWithUIDExists($uid))
{
    echo "true";
}
else
{
    echo 'false';
}
?>
