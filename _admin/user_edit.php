<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');

$script_start_tag = $page->create_open_tag('script', array('src'=>'js/user_edit.js'));
$script_close_tag = $page->create_close_tag('script');
$page->add_head_tag($script_start_tag.$script_close_tag);

//Add Jquery validator
$script_start_tag = $page->create_open_tag('script', array('src'=>'/js/jquery.validate.js'));
$page->add_head_tag($script_start_tag.$script_close_tag);

$hidden='';
if(!isset($_GET['uid']))
{
    $hidden='style="display: none"';
}

    $page->body .= '
<div id="content">
    Select User: <select id="user_select"></select>
    <form method="post" id="form">
    <fieldset id="user_data" '.$hidden.'>
        <legend id="uid"></legend>
        <table>
            <tr>
                <th>Username:</th>
                <td><input type="text" name="uid" id="uid_edit"/><input type="hidden" name="old_uid" id="old_uid"/></td>
                <td><label id="dn"></label></td>
            </tr>
            <tr>
                <th>First Name:</th>
                <td><input type="text" name="givenName" id="givenName"/></td>
            </tr>
            <tr>
                <th>Last Name:</th>
                <td><input type="text" name="sn" id="sn"/></td>
            </tr>
            <tr>
                <th>Burner Name:</th>
                <td><input type="text" name="displayName" id="displayName"/></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><input type="text" name="mail" id="mail"/></td>
            </tr>
            <tr>
                <th>Mobile Number:</th>
                <td><input type="text" name="mobile" id="mobile"/></td>
            </tr>
            <tr>
                <th>Street Address:</th>
                <td><input type="text" name="postalAddress" id="postalAddress"/></td>
            </tr>
            <tr>
                <th>Zip Code:</th>
                <td><input type="text" name="postalCode" id="postalCode"/></td>
            </tr>
            <tr>
                <th>City:</th>
                <td><input type="text" name="l" id="l"/></td>
            </tr>
            <tr>
                <th>State:</th>
                <td>
                    <select name="st" id="st">
                    <option value=""></option>
                    <option value="AL">Alabama</option>
                    <option value="AK">Alaska</option>
                    <option value="AS">American Samoa</option>
                    <option value="AZ">Arizona</option>
                    <option value="AR">Arkansas</option>
                    <option value="AA">Armed Forces Americas</option>
                    <option value="AP">Armed Forces Pacific</option>
                    <option value="AE">Armed Forces Others</option>
                    <option value="CA">California</option>
                    <option value="CO">Colorado</option>
                    <option value="CT">Connecticut</option>
                    <option value="DE">Delaware</option>
                    <option value="DC">District Of Columbia</option>
                    <option value="FL">Florida</option>
                    <option value="GU">Guam</option>
                    <option value="GA">Georgia</option>
                    <option value="HI">Hawaii</option>
                    <option value="ID">Idaho</option>
                    <option value="IL">Illinois</option>
                    <option value="IN">Indiana</option>
                    <option value="IA">Iowa</option>
                    <option value="KS">Kansas</option>
                    <option value="KY">Kentucky</option>
                    <option value="LA">Louisiana</option>
                    <option value="ME">Maine</option>
                    <option value="MD">Maryland</option>
                    <option value="MA">Massachusetts</option>
                    <option value="MI">Michigan</option>
                    <option value="MN">Minnesota</option>
                    <option value="MS">Mississippi</option>
                    <option value="MO">Missouri</option>
                    <option value="MT">Montana</option>
                    <option value="NE">Nebraska</option>
                    <option value="NV">Nevada</option>
                    <option value="NH">New Hampshire</option>
                    <option value="NJ">New Jersey</option>
                    <option value="NM">New Mexico</option>
                    <option value="NY">New York</option>
                    <option value="NC">North Carolina</option>
                    <option value="ND">North Dakota</option>
                    <option value="MP">Northern Mariana Islands</option>
                    <option value="OH">Ohio</option>
                    <option value="OK">Oklahoma</option>
                    <option value="OR">Oregon</option>
                    <option value="PA">Pennsylvania</option>
                    <option value="PR">Puerto Rico</option>
                    <option value="RI">Rhode Island</option>
                    <option value="SC">South Carolina</option>
                    <option value="SD">South Dakota</option>
                    <option value="TN">Tennessee</option>
                    <option value="TX">Texas</option>
                    <option value="UM">United States Minor Outlying Islands</option>
                    <option value="UT">Utah</option>
                    <option value="VT">Vermont</option>
                    <option value="VI">Virgin Islands</option>
                    <option value="VA">Virginia</option>
                    <option value="WA">Washington</option>
                    <option value="WV">West Virginia</option>
                    <option value="WI">Wisconsin</option>
                    <option value="WY">Wyoming</option>
                    </select>
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
