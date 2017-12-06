<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesLeadPage.php');
$page = new ProfilesLeadPage('Burning Flipside Profiles - Lead');

$auth = AuthProvider::getInstance();
$leadGroup = $auth->getGroupByName('Leads');
$aarGroup  = $auth->getGroupByName('AAR');
$afGroup   = $auth->getGroupByName('AFs');
$ccGroup   = $auth->getGroupByName('CC');

$lead_count = $leadGroup->member_count();
$aar_count  = $aarGroup->member_count();
$af_count   = $afGroup->member_count();
$cc_count   = $ccGroup->member_count();

$page->body .= '
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Dashboard</h1>
    </div>       
</div>
<div class="row">';

$page->add_card('fa-user', $lead_count, 'Leads', 'directory.php?filter=lead');
$page->add_card('fa-bullhorn', $af_count, 'AFs', 'directory.php?filter=af', $page::CARD_GREEN);
$page->add_card('fa-cc', $cc_count, 'Combustion Chamber', 'directory.php?filter=cc', $page::CARD_YELLOW);
$page->add_card('fa-fire', $aar_count, 'Board Members', 'directory.php?filter=aar', $page::CARD_RED);

$page->body .= '</div>';

$page->printPage();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
