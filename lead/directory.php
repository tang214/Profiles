<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesLeadPage.php');
$page = new ProfilesLeadPage('Burning Flipside Profiles - Lead');

$page->add_js(JS_CRYPTO_MD5_JS);
$page->add_js_from_src('js/directory.js');

$query = '?fmt=csv';
if(isset($_GET['filter']))
{
    $query = '?type='.$_GET['filter'].'&fmt=csv';
}

$page->body .= '
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Lead Directory</h1>
    </div>       
</div>
<div class="row">
    <table class="table" id="directory" style="cursor: pointer;">
        <thead>
            <th>Legal Name</th>
            <th>Burner Name</th>
            <th>Position</th>
        </thead>
        <tbody></tbody>
    </table>
</div>
<div class="row">
Export: <a href="../api/v1/leads'.$query.'"><img src="../images/csv.svg"/></a>
</div>';

$page->print_page();
?>
