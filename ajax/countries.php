<?php
require_once("static.countries.php");
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'GET')
{
    echo json_encode(array('success'=>0, 'countries'=>$countries));
}
else
{
    echo json_encode(array('error' => "Unrecognized Operation ".$_SERVER['REQUEST_METHOD']));
    die();
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
