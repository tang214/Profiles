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

//Add picture cropper

$css_tag = $page->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'css/croppic.css', 'type'=>'text/css'), true);
$page->add_head_tag($css_tag);

$script_start_tag = $page->create_open_tag('script', array('src'=>'js/croppic.js'));
$script_close_tag = $page->create_close_tag('script');
$page->add_head_tag($script_start_tag.$script_close_tag);

//Add Jquery validator
$script_start_tag = $page->create_open_tag('script', array('src'=>'js/jquery.validate.js'));
$page->add_head_tag($script_start_tag.$script_close_tag);

//Page specific JS
$script_start_tag = $page->create_open_tag('script', array('src'=>'js/profile.js'));
$page->add_head_tag($script_start_tag.$script_close_tag);

$page->body = '
<div id="content">
    <fieldset>
    <legend>Main Profile:</legend>
    <form role="form" action="profile.php" method="post" name="profile" id="profile">
        <input type="hidden" name="uid" id="uid" />
        <div class="form-group">
            <label class="col-sm-2 control-label">Username:</label>
            <div class="col-sm-10">
                <label id="uid_label"></label>
            </div>
        </div>
        <div class="form-group">
            <label for="givenName" class="col-sm-2 control-label">First Name:</label>
            <div class="col-sm-10">
                <input id="givenName" name="givenName" type="text" required/>
            </div>
        </div>
        <div class="form-group">
            <label for="sn" class="col-sm-2 control-label">Last Name:</label>
            <div class="col-sm-10">
                <input id="sn" name="sn" type="text" required/>
            </div>
        </div>
        <div class="form-group">
            <label for="displayName" class="col-sm-2 control-label">Burner Name:</label>
            <div class="col-sm-10">
                <input id="displayName" name="displayName" type="text" />
            </div>
        </div>
        <div class="form-group">
            <label for="mail" class="col-sm-2 control-label">Email:</label>
            <div class="col-sm-10">
                <input id="mail" name="mail" type="text" required/>
            </div>
        </div>
        <div class="form-group">
            <label for="mobile" class="col-sm-2 control-label">Cell Number:</label>
            <div class="col-sm-10">
                <input id="mobile" name="mobile" type="text"/>
            </div>
        </div>
        <div class="form-group">
            <label for="c" class="col-sm-2 control-label">Country:</label>
            <div class="col-sm-10">
                <select id="c" name="c"></select>
            </div>
        </div>
        <div class="clearfix visible-md visible-lg"></div>
        <div class="form-group">
            <label for="street" class="col-sm-2 control-label">Street Address:</label>
            <div class="col-sm-10">
                <input id="street" name="street" type="text"/>
            </div>
        </div> 
        <div class="form-group">
            <label for="zip" class="col-sm-2 control-label">Postal/Zip Code:</label>
            <div class="col-sm-10">
                <input id="zip" name="zip" type="text"/>
            </div>
        </div>
        <div class="form-group">
            <label for="l" class="col-sm-2 control-label">City:</label>
            <div class="col-sm-10">
                <input id="l" name="l" type="text"/>
            </div>
        </div>
        <div class="form-group">
            <label for="st" class="col-sm-2 control-label">State:</label>
            <div class="col-sm-10">
                <select id="st" name="st" type="text"></select>
            </div>
        </div>
        <div class="clearfix visible-md visible-lg"></div>
        <div class="form-group imgCropper">
            <label for="jpegPhoto" class="col-sm-2 control-label">Profile Photo:</label>
            <div class="col-sm-10">
                <div id="jpegPhoto"></div>
            </div>
        </div>
        <div class="clearfix visible-md visible-lg"></div>
        <div class="col-sm-2">
            <input type="reset" value="Discard Changes" id="reset"/>
        </div>
        <div class="col-sm-2">
            <input type="submit" value="Save Changes" id="submit"/>
        </div>
    </form>
    </fieldset>
</div>';

$page->print_page();
/* vim: set tabstop=4 shiftwidth=4 expandtab:*/
?>
