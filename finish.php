<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if(!isset($_GET['hash']))
{
    die('No hash set!');
}
require_once("class.FlipsideLDAPServer.php");
require_once("class.FlipsideUser.php");
$user = FlipsideUser::get_temp_user_by_hash($_GET['hash']);
if($user == FALSE)
{
    die('Unable to obtain user. Please reregister.');
}
$server = new FlipsideLDAPServer();
$user->resetServer($server);
$server->writeObject($user);
$user->eraseFromTempDB($_GET['hash']);
?>
<script type="text/javascript">
<!--
window.location = "index.php"
//-->
</script>
