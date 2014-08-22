<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Redirect users to https
if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
require_once("class.FlipSession.php");
if(!FlipSession::is_logged_in())
{
    header("Location: login.php");
    exit();
}
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles');

//Add picture cropper

$css_tag = $page->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'css/croppic.css', 'type'=>'text/css'), true);
$page->add_head_tag($css_tag);

$script_start_tag = $page->create_open_tag('script', array('src'=>'js/croppic.js'));
$script_close_tag = $page->create_close_tag('script');
$page->add_head_tag($script_start_tag.$script_close_tag);

//Add Jquery validator
$script_start_tag = $page->create_open_tag('script', array('src'=>'js/jquery.validate.js'));
$page->add_head_tag($script_start_tag.$script_close_tag);

//Page specific JS
$script_start_tag = $page->create_open_tag('script', array('src'=>'js/profile.js'));
$page->add_head_tag($script_start_tag.$script_close_tag);

$page->body = '
<div id="content">
    <fieldset>
    <legend>Main Profile:</legend>
    <form action="profile.php" method="post" name="profile" id="profile">
        <input type="hidden" name="uid" id="uid" />
        <table>
            <tr>
                <td>Username:</td>
                <td id="uid_label"></td>
            </tr>
            <tr>
                <td>First Name:</td>
                <td><input id="givenName" name="givenName" type="text"></td>
            </tr>
            <tr>
                <td>Last Name:</td>
                <td><input id="sn" name="sn" type="text"></td>
            </tr>
            <tr>
                <td>Burner Name:</td>
                <td><input id="displayName" name="displayName" type="text"></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><input id="mail" name="mail" type="text"></td>
            </tr>
            <tr>
                <td>Cell Number:</td>
                <td><input id="mobile" name="mobile" type="text"></td>
            </tr>
            <tr>
                <td>Street:</td>
                <td><input id="street" name="street" type="text"></td>
            </tr>
            <tr>
                <td>Postal/Zip Code:</td>
                <td><input id="zip" name="zip" type="text"></td>
            </tr>
            <tr>
                <td>City:</td>
                <td><input id="l" name="l" type="text"></td>
                <td>State:</td>
                <td>
                    <select id="st" name="st">
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
                <td>Profile Photo:</td>
                <td colspan="2"><div id="jpegPhoto"></td>               
            </tr>
            <tr>
                <td><input type="reset" value="Discard Changes" id="reset"/></td>
                <td><input type="submit" value="Save Changes" id="submit"/></td>
            </tr>
        </table>
    </form>
    </fieldset>
</div>';

$page->print_page();
/* vim: set tabstop=4 shiftwidth=4 expandtab:*/
?>
