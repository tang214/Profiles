<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles');
$page->addJSByURI('js/user_exists.js');

$auth = AuthProvider::getInstance();
$email = false;
if(!isset($_GET['src']))
{
    die('Error loading page. Authentication source (src) must be specified');
}
$provider = $auth->getSuplementalProviderByHost($_GET['src']);
if($provider !== false)
{
    $user = $provider->getUserFromToken(false);
    $email = $user->mail;
}

$page->body .= '
<div id="content">
    <h1>That user already exists!</h1>
    <p>Only one user can exist per email address and there is already a user present for <b>'.$email.'</b>.</p>
    <p>Would you like to link the two accounts so that you can login with Google now and in the future? Or would you like to login with your Burning Flipside Profiles account?</p>
    <p><button data-toggle="modal" data-target="#link-dialog" type="button" class="btn btn-warning">Link Accounts</button> <button data-toggle="modal" data-target="#login-dialog" type="button" class="btn btn-primary">Login</button></p>
    <div class="modal fade" role="dialog" id="link-dialog" title="Login" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">Link Accounts</h4>
                </div>
                <div class="modal-body">
                    <p>Please enter the password to the Burning Flipside Profile\'s account to prove that you own the account.</p>
                    <input class="form-control" type="password" name="link_pass" id="link_pass" placeholder="Password" required="" aria-required="true">
                    <button class="btn btn-lg btn-warning btn-block" type="submit" onclick="link_accounts()">Link</button>
                </div>
            </div>
        </div>
    </div>
';

$page->printPage();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
