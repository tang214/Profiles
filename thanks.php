<?php
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles');

$page->body .= '
<div id="content">
    <h1>Thanks for registering!</h1>
    <p>You should receive an email shortly. This email will contain instructions needed to complete your registration.</p>
</div>';

$page->printPage();
?>
