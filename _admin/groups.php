<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');
$page->add_js(JS_DATATABLE, false);
$page->add_css(CSS_DATATABLE);
$page->add_js_from_src('js/groups.js');


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

$page->print_page();
?>
