<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');

$script_start_tag = $page->create_open_tag('script', array('src'=>'js/index.js'));
$script_close_tag = $page->create_close_tag('script');
$page->add_head_tag($script_start_tag.$script_close_tag);


$page->body .= '
<div id="content">
    <div id="accordian">
        <h3>Current Users</h3>
        <div>
            <table id="user_table">
                <thead>
                    <th>User Name</th>
                    <th>Burner Name</th>
                    <th>Legal Name</th>
                    <th>Email</th>
                </thead>
            </table>
        </div>
        <h3>Pending Registrations</h3>
        <div id="pending_set">
            <select name="pending_action" id="pending_action">
                <option value="none">Action...</option>
                <option value="del">Delete</option>
            </select>
            <input type="button" value="Apply" onclick="pendingExecute()"/>
            <table id="pending_table">
                <thead>
                    <th>User Name</th>
                    <th>Legal Name</th>
                    <th>Email</th>
                </thead>
            </table>
        </div>
        <h3>Groups</h3>
        <div>
            <table id="group_table">
                <thead>
                    <th>Group Name</th>
                    <th>Description</th>
                </thead>
            </table>
        </div>
        <h3>Sessions</h3>
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
            </table>
        </div>
    </div>
</div>';

$page->print_page();
?>
