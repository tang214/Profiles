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
            <table>
                <tr>
                    <th>Group Name:</th>
                    <td><input type="text" name="gid" id="gid_edit"/><input type="hidden" name="old_gid" id="old_gid"/></td>
                    <td><label id="dn"></label></td>
                </tr>
                <tr>
                    <th>Description:</th>
                    <td><input type="text" name="description" id="description"/></td>
                </tr>
            </tr>
            <tr>
                <td colspan=3">
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
                </td>
            </tr>
            <tr>
                <td><input type="submit" value="Submit Changes" id="submit"/></td>
            </tr>
        </table>
    </fieldset>
    </form>
</div>';

$page->print_page();
?>
