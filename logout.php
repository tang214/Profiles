<?php
require_once("class.FlipSession.php");
FlipSession::end();
require_once('class.ProfilesPage.php');
$page = new ProfilesPage('Burning Flipside Profiles');

$page->body .= '
<div id="content">
    You have been logged out.
</div>
<script>
    function send_to_index()
    {
        window.location.href="index.php";
    }
    setTimeout(send_to_index, 5000);
</script>';

$page->printPage();
?>
