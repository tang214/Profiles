<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');
$page->add_js(JS_DATATABLE, false);
$page->add_css(CSS_DATATABLE);
$page->add_js_from_src('js/sessions.js');

$page->body .= '
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Sessions</h1>
    </div>       
</div>
<div>
    <select name="session_action" id="session_action">
        <option value="none">Action...</option>
        <option value="del">End Session</option>
    </select>
    <input type="button" value="Apply" onclick="sessionExecute()"/>
    <table id="sessions">
        <thead>
            <th>Session ID</th>
            <th>Username</th>
            <th>IP Address</th>
            <th>Init Time</th>
        </thead>
        <tbody></tbody>
    </table>
</div>';

$page->print_page();
?>
