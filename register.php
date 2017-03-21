<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Redirect users to https
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
    exit();
}
require_once('class.FlipsideCAPTCHA.php');
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles Registration');
$page->addJSByURI('/js/zxcvbn-async.js');
$page->addJSByURI('/js/register.js');

$captcha = new FlipsideCAPTCHA();
FlipSession::setVar('captcha', $captcha);

if(isset($_GET['return']))
{
    $return = '<input type="hidden" name="return" value="'.$_GET['return'].'"/>';
}
else
{
    $return = '';
}

if(FlipSession::isLoggedIn())
{
    $page->addNotification('You are currently logged in to the system. Are you sure you want to register another account?');
}

$page->body .= '
<div id="content">
    <form name="form" id="form" role="form">
        <fieldset>
            <legend>Burning Flipside Profile Registration</legend>
        </fieldset>
        <div class="form-group">
            <label for="email" class="col-sm-2 control-label">Email:</label>
            <div class="col-sm-10">
                <input class="form-control" type="email" name="mail" id="email" data-toggle="tooltip" data-placement="top" title="The email to use for this account. NOTE: You must be able to verify you own this email address by responding to an email." required/>
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="row" style="min-height: 15px;"></div>
        <div class="form-group">
            <label for="uid" class="col-sm-2 control-label">Username:</label>
            <div class="col-sm-10">
                <input class="form-control" type="text" name="uid" id="uid" required/>
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="row" style="min-height: 15px;"></div>
        <div class="form-group">
            <label for="password" class="col-sm-2 control-label">Password:</label>
            <div class="col-sm-10">
                <input type="password" name="password" id="password" class="pass form-control" data-toggle="tooltip" data-placement="top" title="Your password must be at least 4 characters long, contain a lower case letter, uppercase letter, and a number." required/>
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="row" style="min-height: 15px;"></div>
        <div class="form-group">
            <label for="password2" class="col-sm-2 control-label">Confirm Password:</label>
            <div class="col-sm-10">
                <input class="form-control" type="password" name="password2" id="password2" required/>
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="row" style="min-height: 15px;"></div>
        <div class="form-group">
            '.$captcha->draw_captcha(true, true).'
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        '.$return.'
        <div class="form-group">
            <div class="col-sm-2">
            <button id="submit" type="submit" name="submit" class="btn btn-default">Register</button>
            </div>
        </div>
    </form>
</div>';

$page->printPage();
// vim: set tabstop=4 shiftwidth=4 expandtab:
?>


