<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');
$page->addWellKnownJS(JS_DATATABLE, false);
$page->addWellKnownCSS(CSS_DATATABLE);
$page->addJSByURI('js/groups.js');


$page->body .= '
<div class="col-lg-12">
    <h1 class="page-header">Groups</h1>
</div>
<div>
    <select name="group_action" id="group_action">
        <option value="none">Action...</option>
        <option value="del">Delete Group</option>
        <option value="new">Add New Group...</option>
    </select>
    <input type="button" value="Apply" onclick="groupExecute()"/>
    <table id="group_table">
        <thead>
            <th>Group Name</th>
            <th>Description</th>
        </thead>
        <tbody></tbody>
    </table>
</div>';

$page->printPage();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
