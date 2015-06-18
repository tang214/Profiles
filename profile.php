<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Redirect users to https
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipSession.php");
if(!FlipSession::is_logged_in())
{
    header("Location: login.php");
    exit();
}
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles');

$page->add_js(JS_CRYPTO_MD5_JS);
//Add picture cropper
$page->add_css_from_src('css/croppic.css');
$page->add_js_from_src('js/croppic.js');
$page->add_js_from_src('js/profile.js');

$page->add_notification('All the information on this page is optional. However, it will make the process of signing up for Ticket Requests, Theme Camp Registrations, Art Project Registrations, and Volunteer Signup faster and easier. If you have any concerns with providing this information we suggest your read our <a href="http://www.burningflipside.com/about/privacy" class="alert-link" target="_new">Privacy Policy</a> or contact the <a href="http://www.burningflipside.com/contact/Website%20Feedback" class="alert-link" target="_new">Technology Team</a> or the <a href="http://www.burningflipside.com/contact/AAR-LLC" class="alert-link" target="_new">AAR Board of Directors</a> with your concerns.', $page::NOTIFICATION_INFO);

$page->body = '
<div id="content">
    <fieldset>
    <legend>Main Profile:</legend>
    <form role="form" action="profile.php" method="post" name="profile" id="profile">
        <input type="hidden" name="uid" id="uid" />
        <div class="form-group">
            <label class="col-sm-2 control-label">Username:</label>
            <div class="col-sm-10">
                <label class="form-control" id="uid_label" disabled></label>
            </div>
        </div>
        <div class="form-group">
            <label for="mail" class="col-sm-2 control-label">Email:</label>
            <div class="col-sm-10">
                <input class="form-control" id="mail" name="mail" type="text" readonly/>
            </div>
        </div>
        <div class="form-group">
            <label for="givenName" class="col-sm-2 control-label">First Name:</label>
            <div class="col-sm-10">
                <input class="form-control" id="givenName" name="givenName" type="text"/>
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="form-group">
            <label for="sn" class="col-sm-2 control-label">Last Name:</label>
            <div class="col-sm-10">
                <input class="form-control" id="sn" name="sn" type="text" required/>
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="form-group">
            <label for="displayName" class="col-sm-2 control-label">Burner Name:</label>
            <div class="col-sm-10">
                <input class="form-control" id="displayName" name="displayName" type="text" />
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="form-group">
            <label for="c" class="col-sm-2 control-label">Country:</label>
            <div class="col-sm-10">
                <select class="form-control bfh-countries" id="c" name="c" data-country="US"></select>
            </div>
        </div>
        <div class="form-group">
            <label for="mobile" class="col-sm-2 control-label">Cell Number:</label>
            <div class="col-sm-10">
                <input class="form-control bfh-phone" data-country="c" id="mobile" name="mobile" type="text"/>
            </div>
        </div>
        <div class="clearfix visible-md visible-lg"></div>
        <div class="form-group">
            <label for="postalAddress" class="col-sm-2 control-label">Street Address:</label>
            <div class="col-sm-10">
                <textarea class="form-control" id="postalAddress" rows="2" name="postalAddress" type="text"></textarea>
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="form-group">
            <label for="postalCode" class="col-sm-2 control-label">Postal/Zip Code:</label>
            <div class="col-sm-10">
                <input class="form-control" id="postalCode" name="postalCode" type="text"/>
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="form-group">
            <label for="l" class="col-sm-2 control-label">City:</label>
            <div class="col-sm-10">
                <input class="form-control" id="l" name="l" type="text"/>
            </div>
        </div>
        <div class="form-group">
            <label for="st" class="col-sm-2 control-label">State:</label>
            <div class="col-sm-10">
                <select class="form-control bfh-states" data-country="c" id="st" name="st" type="text"></select>
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="form-group imgCropper">
            <label for="jpegPhoto" class="col-sm-2 control-label">Profile Photo:</label>
            <div class="col-sm-4">
                <div id="jpegPhoto"></div>
            </div>
            <div class="col-sm-4">
                <div id="gravatar"></div>
            </div>
        </div>
        <div class="clearfix visible-md visible-lg"></div>
        <div class="col-sm-2">
            <button class="btn btn-default" type="button" id="submit" onclick="update_profile()">Save Changes</button>
        </div>
    </form>
    </fieldset>
    <fieldset>
        <legend>Other Options:</legend>
        <button class="btn btn-default" onclick="delete_user()">Delete My Account&hellip;</button>
    </fieldset>
</div>';

$page->print_page();
/* vim: set tabstop=4 shiftwidth=4 expandtab:*/
?>
