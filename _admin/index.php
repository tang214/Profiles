<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');

$page->add_js_from_src('js/index.js');

$auth = AuthProvider::getInstance();
$user_count = $auth->getActiveUserCount(false);
$temp_user_count = $auth->getPendingUserCount();
$group_count = $auth->getGroupCount();

$sessions = FlipSession::get_all_sessions();
$session_count = 0;
if($sessions !== false)
{
    $session_count = count($sessions);
}


$page->body .= '
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Dashboard</h1>
    </div>       
</div>
<div class="row">';

$page->add_card('fa-user', $user_count, 'Users', 'users_current.php');
$page->add_card('fa-inbox', $temp_user_count, 'Pending Users', 'users_pending.php', $page::CARD_GREEN);
$page->add_card('fa-users', $group_count, 'Groups', 'groups.php', $page::CARD_RED);
$page->add_card('fa-cloud', $session_count, 'Sessions', 'sessions.php', $page::CARD_YELLOW);

$page->body .= '</div>';

$page->print_page();
?>
