<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');

$script_start_tag = $page->create_open_tag('script', array('src'=>'js/group_new.js'));
$script_close_tag = $page->create_close_tag('script');
$page->add_head_tag($script_start_tag.$script_close_tag);

//Add Jquery validator
$script_start_tag = $page->create_open_tag('script', array('src'=>'/js/jquery.validate.js'));
$page->add_head_tag($script_start_tag.$script_close_tag);

    $page->body .= '
<div id="content">
    <form method="post" id="form">
        <fieldset id="group_data">
            <table>
                <tr>
                    <th>Group Name:</th>
                    <td><input type="text" name="gid" id="gid" required/></td>
                    <td><label for="gid" class="error"></label></td>
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
