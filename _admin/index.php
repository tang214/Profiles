<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');

$page->body .= '
<div id="content">
    <table>
        <tr>
            <td>
                <fieldset>
                    <legend>Current Users</legend>
                    <table id="user_table">
                        <thead>
                            <th>User Name</th>
                            <th>Burner Name</th>
                            <th>Legal Name</th>
                            <th>Email</th>
                        </thead>
                    </table>
                </fieldset>
            </td>
            <td>
                <fieldset id="pending_set">
                    <legend>Pending Registrations</legend>
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
                </fieldset>
            </td>
        </tr>
        <tr>
            <td>
                <fieldset>
                    <legend>Groups</legend>
                    <table id="group_table">
                        <thead>
                            <th>Group Name</th>
                            <th>Description</th>
                        </thead>
                    </table>
                </fieldset>
            </td>
        </tr>
    </table>
</div>';

$page->print_page();
?>
