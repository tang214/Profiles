<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles Reset');
$page->addWellKnownJS(JS_BOOTBOX);
$page->addJSByURI('js/reset.js');

if($page->user !== false && $page->user !== null)
{
    //User is logged in. They can reset their password...
    $page->body = '
        <div id="content">
            <h3>Burning Flipside Password Reset</h3>
            <div class="form-group">
                <label for="oldpass" class="col-sm-2 control-label">Current Password:</label>
                <div class="col-sm-10">
                    <input class="form-control" type="password" name="oldpass" id="oldpass" required/>
                </div>
            </div>
            <div class="clearfix visible-sm visible-md visible-lg"></div>
            <div class="form-group">
                <label for="newpass" class="col-sm-2 control-label">New Password:</label>
                <div class="col-sm-10">
                    <input class="form-control" type="password" name="newpass" id="newpass" required/>
                </div>
            </div>
            <div class="clearfix visible-sm visible-md visible-lg"></div>
            <div class="form-group">
                <label for="confirm" class="col-sm-2 control-label">Confirm Password:</label>
                <div class="col-sm-10">
                    <input class="form-control" type="password" name="confirm" id="confirm" required/>
                </div>
            </div>
            <div class="clearfix visible-sm visible-md visible-lg"></div>
            <button name="submit" class="btn btn-primary" onclick="change_password();">Change Password</button>
        </div>
    ';
}
else
{
    $page->body .= '
        <div id="content">
            <h3>Burning Flipside Login Reset/Recover</h3>
            <div class="radio">
                <label>
                    <input type="radio" name="forgot" value="user"/>
                    Forgot Username
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="forgot" value="pass"/>
                    Forgot Password
                </label>
            </div>
            <div class="clearfix visible-sm visible-md visible-lg"></div>
            <button name="submit" class="btn btn-primary" onclick="what_did_they_forget();">Next</button>
        </div>';
}

$page->printPage();
// vim: set tabstop=4 shiftwidth=4 expandtab:
?>


