<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesLeadPage.php');
$page = new ProfilesLeadPage('Burning Flipside Profiles - Lead');

$auth = AuthProvider::getInstance();
$leadGroup = $auth->get_group_by_name(false, 'Leads');
$aarGroup  = $auth->get_group_by_name(false, 'AAR');
$afGroup   = $auth->get_group_by_name(false, 'AFs');
$ccGroup   = $auth->get_group_by_name(false, 'CC');

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
            <a href="directory.php?filter=cc">
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
