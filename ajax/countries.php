<?php
require_once("static.countries.php");
require_once("class.FlipJax.php");
class CountryAjax extends FlipJax
{
    function get($params)
    {
        global $countries;
        return array('countries'=>$countries);
    }
}

$ajax = new CountryAjax();
$ajax->run();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
