<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');

$page->addJS(JS_BOOTSTRAP_FH);
$page->addJSFromSrc('js/user_edit.js');

$hidden='';
if(!isset($_GET['uid']))
{
    $hidden='style="display: none"';
}

    $page->body .= '
<div id="content">
    Select User: <select id="user_select"></select>
    <form method="post" id="form">
    <fieldset id="user_data" '.$hidden.'>
        <legend id="uid"></legend>
        <div class="form-group">
            <label for="uid" class="col-sm-2 control-label">Username:</label>
            <div class="col-sm-10">
                <input class="form-control" id="uid_x" name="uid" type="text" readonly/>
                <input type="hidden" name="old_uid" id="old_uid"/>
            </div>
        </div>
        <div class="form-group">
            <label for="givenName" class="col-sm-2 control-label">First Name:</label>
            <div class="col-sm-10">
                <input class="form-control" id="givenName" name="givenName" type="text">
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="form-group">
            <label for="sn" class="col-sm-2 control-label">Last Name:</label>
            <div class="col-sm-10">
                <input class="form-control" id="sn" name="sn" type="text" required="" aria-required="true">
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="form-group">
            <label for="displayName" class="col-sm-2 control-label">Burner Name:</label>
            <div class="col-sm-10">
                <input class="form-control" id="displayName" name="displayName" type="text">
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="form-group">
            <label for="mail" class="col-sm-2 control-label">Email:</label>
            <div class="col-sm-10">
                <input class="form-control" id="mail" name="mail" type="text" readonly="">
            </div>
        </div>
        <div class="form-group">
            <label for="c" class="col-sm-2 control-label">Country:</label>
            <div class="col-sm-10">
                <select class="form-control bfh-countries" id="c" name="c" data-country="US"></select>
            </div>
        </div>
        <div class="form-group">
            <label for="mobile" class="col-sm-2 control-label">Cell Number:</label>
            <div class="col-sm-10">
                <input class="form-control bfh-phone" data-country="c" id="mobile" name="mobile" type="text">
            </div>
        </div>
        <div class="clearfix visible-md visible-lg"></div>
        <div class="form-group">
            <label for="street" class="col-sm-2 control-label">Street Address:</label>
            <div class="col-sm-10">
                <textarea class="form-control" id="postalAddress" rows="2" name="postalAddress" type="text"></textarea>
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="form-group">
            <label for="zip" class="col-sm-2 control-label">Postal/Zip Code:</label>
            <div class="col-sm-10">
                <input class="form-control" id="postalCode" name="postalCode" type="text">
            </div>
        </div>
        <div class="clearfix visible-sm visible-md visible-lg"></div>
        <div class="form-group">
            <label for="l" class="col-sm-2 control-label">City:</label>
            <div class="col-sm-10">
                <input class="form-control" id="l" name="l" type="text">
            </div>
        </div>
        <div class="form-group">
            <label for="st" class="col-sm-2 control-label">State:</label>
            <div class="col-sm-10">
                <select class="form-control bfh-states" data-country="c" id="st" name="st" type="text"></select>
            </div>
        </div>
        <div class="form-group">
            <label for="st" class="col-sm-2 control-label">Area:</label>
            <div class="col-sm-10">
                <select class="form-control" id="ou" name="ou" onchange="area_change(this)">
                    <option></option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="st" class="col-sm-2 control-label">Position:</label>
            <div class="col-sm-10">
                <select class="form-control" id="title" name="title">
                    <option></option>
                </select>
            </div>
        </div>
        <button class="btn btn-default" type="submit" id="submit">Submit Changes</button>
    </fieldset>
    </form>
</div>';

$page->print_page();
?>
