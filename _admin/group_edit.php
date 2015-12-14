<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');

$page->add_js(JS_DATATABLE, false);
$page->add_css(CSS_DATATABLE);
$page->add_js_from_src('js/group_edit.js');

$hidden='';
if(!isset($_GET['gid']))
{
    $hidden='style="display: none"';
}

    $page->body .= '
<div id="content">
    Select Group: <select id="group_select"></select>
    <form method="post" id="form">
        <fieldset id="group_data" '.$hidden.'>
            <legend id="gid"></legend>
            <div class="form-group">
                <label class="col-sm-2 control-label">Group Name:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="gid" id="gid_edit" disabled="true"/>
               </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Description:</label>
                <div class="col-sm-10">
                    <textarea class="form-control" name="description" id="description"></textarea>
               </div>
            </div>
            <div class="clearfix visible-sm visible-md visible-lg"></div>
            <fieldset>
                <legend>Members</legend>
                <table id="members" class="table">
                    <thead>
                        <th></th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Name</th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </fieldset>
            <div class="clearfix visible-sm visible-md visible-lg"></div>
            <fieldset>
                <legend>Non-Members</legend>
                <table id="non-members" class="table">
                    <thead>
                        <th></th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Name</th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </fieldset>
            <input type="submit" class="form-control" value="Submit Changes" id="submit"/>
    </fieldset>
    </form>
</div>';

$page->print_page();
?>
