<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require('Autoload.php');
require('class.AreasAPI.php');
require('class.GroupsAPI.php');
require('class.LeadsAPI.php');
require('class.PendingUserAPI.php');
require('class.SessionsAPI.php');
require('class.UsersAPI.php');
require('class.ProfilesAPI.php');

$site = new \Http\WebSite();

$site->registerAPI('/areas', new AreasAPI());
$site->registerAPI('/groups', new GroupsAPI());
$site->registerAPI('/leads', new LeadsAPI());
$site->registerAPI('/pending_users', new PendingUserAPI());
$site->registerAPI('/users', new UsersAPI());
$site->registerAPI('/sessions', new SessionsAPI());
$site->registerAPI('', new ProfilesAPI());
$site->run();
