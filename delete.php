<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Redirect users to https
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipSession.php");
if(!FlipSession::isLoggedIn())
{
    header("Location: login.php");
    exit();
}
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles');

//Page specific JS
$page->addJSByURI('js/delete.js');

$page->body = '
<div id="content">
    <div class="alert alert-danger" role="alert">
        <table>
            <tr>
                <td>
                    <span class="fa fa-fire" style="font-size: 5em;"></span>
                </td>
                <td>
        Please note: This operation is <strong>irreversible</strong>! Once your account is deleted you will no longer have access to 
        any of your ticket requests, theme camp registrations, art proejct registrations or other information you have provided to AAR,
        LLC.
                </td>
            <tr>
        </table>
    </div>
    <input type="checkbox" onclick="allow_delete()">Yes, I understand that this operation is irreversible. Please delete my account.</input><br/><br/><br/>
    <button type="button" class="btn btn-danger" disabled>Delete My Account</button> 
</div>';

$page->printPage();
/* vim: set tabstop=4 shiftwidth=4 expandtab:*/
