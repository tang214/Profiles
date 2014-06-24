<?php
header('Content-Type: application/json');
if(preg_match("/^([0-9]{5})(-[0-9]{4})?$/i",$_GET['zip']))
{
    $contents = file_get_contents('https://zip.getziptastic.com/v2/US/'.$_GET['zip']);
    echo $contents;
}
?>

