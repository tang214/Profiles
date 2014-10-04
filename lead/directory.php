<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesLeadPage.php');
require_once('class.FlipsideLDAPServer.php');
$page = new ProfilesLeadPage('Burning Flipside Profiles - Lead');

$page->add_js_from_src('/js/jquery.dataTables.js');
$page->add_js_from_src('js/directory.js');

$css_tag = $page->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'/css/jquery.dataTables.css', 'type'=>'text/css'), true);
$page->add_head_tag($css_tag);

$server = new FlipsideLDAPServer();
$groups = $server->getGroups("(cn=Leads)");
$members = array();
if($groups != FALSE && isset($groups[0]))
{
    $members = $groups[0]->getMembers(FALSE);
}
$lead_count = count($members);
$groups = $server->getGroups("(cn=AAR)");
$members = array();
if($groups != FALSE && isset($groups[0]))
{
    $members = $groups[0]->getMembers(FALSE);
}
$aar_count = count($members);
$groups = $server->getGroups("(cn=AFs)");
$members = array();
if($groups != FALSE && isset($groups[0]))
{
    $members = $groups[0]->getMembers(FALSE);
}
$af_count = count($members);


$page->body .= '
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Lead Directory</h1>
    </div>       
</div>
<div class="row">
    <table class="table" id="directory">
        <thead>
            <th>Legal Name</th>
            <th>Burner Name</th>
            <th>Position</th>
            <th>Email</th>
            <th>Phone</th>
        </thead>
        <tbody></tbody>
    </table>
</div>
<div class="row">
Export: <a href="directory.csv.php"><img src="/images/csv.svg"/></a>
</div>';

$page->print_page();
?>
