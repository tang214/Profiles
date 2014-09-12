<?php
require_once("static.states.php");
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'GET')
{
    if(!isset($_GET['c']) || !is_string($_GET['c']))
    {
        echo json_encode(array('error' => "Invalid Parameter! Expected c as a string"));
        die();
    }
    if(isset($states[$_GET['c']]))
    {
        echo json_encode(array('success'=>0, 'states'=>$states[$_GET['c']]));
    }
    else
    {
        echo json_encode(array('error'=>'Invalid Parameter! No such country!'));
    }
}
else
{
    echo json_encode(array('error' => "Unrecognized Operation ".$_SERVER['REQUEST_METHOD']));
    die();
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
