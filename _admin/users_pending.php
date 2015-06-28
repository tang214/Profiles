<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');
$page->add_js(JS_DATATABLE);
$page->add_js_from_src('js/pending_users.js');

$page->body .= '
<div class="col-lg-12">
    <h1 class="page-header">Pending Registrations</h1>
</div>
<div id="pending_set">
    <select name="pending_action" id="pending_action">
        <option value="none">Action...</option>
        <option value="del">Delete</option>
    </select>
    <button class="btn btn-default" type="button" onclick="pendingExecute()">Apply</button>
    <table id="pending_table" class="table">
        <thead>
            <th>User Name</th>
            <th>Legal Name</th>
            <th>Email</th>
        </thead>
        <tbody></tbody>
    </table>
</div>';

$page->print_page();
?>
