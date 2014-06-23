<?php
require_once("class.FlipSession.php");
FlipSession::end();
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles');

$page->body = '
<div id="content">
    You have been logged out. Click <a href="login.php">here</a> to log back in.
</div>';

$page->print_page();
?>
