<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesLeadPage.php');
require_once('class.FlipsideLDAPServer.php');
$page = new ProfilesLeadPage('Burning Flipside Profiles - Lead');

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

$groups = $server->getGroups("(cn=CC)");
$members = array();
if($groups != FALSE && isset($groups[0]))
{
    $members = $groups[0]->getMembers(FALSE);
}
$cc_count = count($members);

$page->body .= '
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Dashboard</h1>
    </div>       
</div>
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <span class="glyphicon glyphicon-user" style="font-size: 5em;"></span>                                
                    </div>
                    <div class="col-xs-9 text-right">
                        <div style="font-size: 40px;">'.$lead_count.'</div>
                        <div>Leads</div>
                    </div>
                </div>
            </div>
            <a href="directory.php?filter=lead">
                <div class="panel-footer">
                    <span class="pull-left">View Details</span>
                    <span class="pull-right glyphicon glyphicon-circle-arrow-right"></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-green">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <span class="glyphicon glyphicon-bullhorn" style="font-size: 5em;"></span>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div style="font-size: 40px;">'.$af_count.'</div>
                        <div>AFs</div>
                    </div>
                </div>
            </div>
            <a href="directory.php?filter=af">
                <div class="panel-footer">
                    <span class="pull-left">View Details</span>
                    <span class="pull-right glyphicon glyphicon-circle-arrow-right"></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-yellow">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <span class="glyphicon glyphicon-subtitles" style="font-size: 5em;"></span>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div style="font-size: 40px;">'.$cc_count.'</div>
                        <div>Combustion Chamber</div>
                    </div>
                </div>
            </div>
            <a href="directory?filter=cc">
                <div class="panel-footer">
                    <span class="pull-left">View Details</span>
                    <span class="pull-right glyphicon glyphicon-circle-arrow-right"></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-red">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <span class="glyphicon glyphicon-fire" style="font-size: 5em;"></span>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div style="font-size: 40px;">'.$aar_count.'</div>
                        <div>Board Members</div>
                    </div>
                </div>
            </div>
            <a href="directory.php?filter=aar">
                <div class="panel-footer">
                    <span class="pull-left">View Details</span>
                    <span class="pull-right glyphicon glyphicon-circle-arrow-right"></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>
</div>';

$page->print_page();
?>
