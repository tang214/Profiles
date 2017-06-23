<?php
require_once('Autoload.php');

$now = new DateTime();
$now->modify('-1 day');
$now->setTime(0, 0);
$yesterday = date("Y-m-d H:i:s", $now->getTimestamp());

$auth = \AuthProvider::getInstance();
$filter = new \Data\Filter("time lt '$yesterday'");
$users = $auth->getPendingUsersByFilter($filter);
$count = count($users);
for($i = 0; $i < $count; $i++)
{
    $users[$i]->delete();
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
