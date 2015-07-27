<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');
$page->add_js(JS_DATATABLE, false);
$page->add_css(CSS_DATATABLE);
$page->add_js_from_src('js/users.js');


$page->body .= '
<div class="col-lg-12">
    <h1 class="page-header">Current Users</h1>
</div>
<table id="user_table" class="table">
    <thead>
        <th>User Name</th>
        <th>Burner Name</th>
        <th>Legal Name</th>
        <th>Email</th>
    </thead>
    <tbody></tbody>
</table>
';

$page->print_page();
?>
