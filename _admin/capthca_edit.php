<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');

$page->add_js_from_src('js/captcha_edit.js');

    $page->body .= '
<div id="content">
    Select CAPTCHA: <select id="captcha_select">
        <option value="new">Add new...</option>
    </select>
    <form method="post" id="form">
    <fieldset id="captcha_data">
        <legend id="captcha"></legend>
        <table>
            <tr>
                <th>ID:</th>
                <td><label id="id"></label><input type="hidden" name="id" id="cid"/></td>
            </tr>
            <tr>
                <th>Question:</th>
                <td><input type="text" name="question" id="question" size="100"/></td>
            </tr>
            <tr>
                <th>Answer:</th>
                <td><input type="text" name="answer" id="answer" size="100"/></td>
            </tr>
            <tr>
                <th>Hint:</th>
                <td><input type="text" name="hint" id="hint" size="100"/></td>
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
