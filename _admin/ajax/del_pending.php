<?php
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipSession.php");
$user = FlipSession::get_user(TRUE);
if($user == FALSE || !$user->isInGroupNamed("LDAPAdmins"))
{
    die("Not logged in!");
}
if(strtoupper($_SERVER['REQUEST_METHOD']) != 'POST')
{
    die("Unrecognized Operation "+$_SERVER['REQUEST_METHOD']);
}
else
{
    if(!isset($_POST['uids']) || !is_array($_POST['uids']))
    {
        die("Invalid Parameter! Expected uids as an array");
    }
    $uids = $_POST['uids'];
}
$res = array();
$overall = TRUE;
for($i = 0; $i < count($uids); $i++)
{
    $temp = FlipsideUser::delete_temp_user_by_uid($uids[$i]);
    if($temp == FALSE)
    {
        $overall = FALSE;
    }
    array_push($res, array('uid'=>$uids[$i], 'res'=>$temp));
}
echo json_encode(array('overall'=>$overall, 'individual'=>$res));
?>
