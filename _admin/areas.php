<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');

$page->addJSByURI('js/areas.js');

    $page->body .= '
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Areas</h1>
    </div>       
</div>
<div class="row">
    <div class="form-group">
        <label class="col-sm-2 control-label">Area:</label>
        <div class="col-sm-10">
            <select class="form-control" id="area_select" onchange="area_change(this)">
                <option></option>
                <option value="_new">New...</option>
            </select>
        </div>
    </div>
    <div class="clearfix visible-sm visible-md visible-lg"></div>
</div>
<div class="row" id="area_details" style="display: none;">
    <fieldset>
        <legend id="area_name"></legend>
        <div class="form-group">
            <label class="col-sm-2 control-label">Short Name:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="short_name" required/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Name:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="name" required/>
            </div>
        </div>
        <button class="btn btn-default" id="submit">Submit</button>
    </fieldset>
</div>';

$page->print_page();
?>
