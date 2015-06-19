<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('class.ProfilesAdminPage.php');
$page = new ProfilesAdminPage('Burning Flipside Profiles - Admin');

$page->add_js_from_src('js/index.js');

$auth = AuthProvider::getInstance();
$user_count = $auth->get_active_user_count(false);
$temp_user_count = $auth->get_pending_user_count(false);
$group_count = $auth->get_group_count(false);

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
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <span class="glyphicon glyphicon-user" style="font-size: 5em;"></span>                                
                    </div>
                    <div class="col-xs-9 text-right">
                        <div style="font-size: 40px;">'.$user_count.'</div>
                        <div>Users</div>
                    </div>
                </div>
            </div>
            <a href="users_current.php">
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
                        <span class="glyphicon glyphicon-inbox" style="font-size: 5em;"></span>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div style="font-size: 40px;">'.$temp_user_count.'</div>
                        <div>Pending Users</div>
                    </div>
                </div>
            </div>
            <a href="users_pending.php">
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
                        <span class="glyphicon glyphicon-tower" style="font-size: 5em;"></span>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div style="font-size: 40px;">'.$group_count.'</div>
                        <div>Groups</div>
                    </div>
                </div>
            </div>
            <a href="groups.php">
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
                        <span class="glyphicon glyphicon-cloud" style="font-size: 5em;"></span>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div style="font-size: 40px;">'.$session_count.'</div>
                        <div>Sessions</div>
                    </div>
                </div>
            </div>
            <a href="sessions.php">
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
