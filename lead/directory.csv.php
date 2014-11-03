<?php
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=directory.csv");
header("Pragma: no-cache");
header("Expires: 0");
require_once('class.FlipSession.php');
require_once('class.FlipsideLDAPServer.php');
require_once("_admin/class.FlipsideLead.php");
$user = FlipSession::get_user(TRUE);
if($user == FALSE || (!$user->isInGroupNamed("Leads") && !$user->isInGroupNamed("CC")))
{
    die("Authentication failure");
}

$positions = null;

function title_to_string($title)
{
    if($positions == null)
    {
        $db = new FlipsideDB('registration');
        $positions = FlipsideLead::get_all_of_type($db);
        if($positions == FALSE)
        {
            return $title;
        }
        if(!is_array($positions))
        {
            $positions = array($positions);
        }
    }
    for($i = 0; $i < count($positions); $i++)
    {
        if($positions[$i]->short_name == $title)
        {
            return $positions[$i]->name;
        }
    }
    return $title;
}

function get_directory($filter=FALSE)
{
    $server = new FlipsideLDAPServer();

    $members = array();
    if($filter == FALSE)
    {
        $groups = $server->getGroups("(cn=Leads)");
        if($groups == FALSE || !isset($groups[0]))
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Unable to locate Leads Group!");
        }
        $members = $groups[0]->getMembers();
    }
    else if($filter == 'lead')
    {
        $groups = $server->getGroups("(cn=Leads)");
        if($groups == FALSE || !isset($groups[0]))
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Unable to locate Leads Group!");
        }
        $members = $groups[0]->getMembers(FALSE);
    }
    else if($filter == 'af')
    {
        $groups = $server->getGroups("(cn=AFs)");
        if($groups == FALSE || !isset($groups[0]))
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Unable to locate AFs Group!");
        }
        $members = $groups[0]->getMembers(FALSE);
    }
    else if($filter == 'aar')
    {
        $groups = $server->getGroups("(cn=AAR)");
        if($groups == FALSE || !isset($groups[0]))
        {
            return array('err_code' => self::INTERNAL_ERROR, 'reason' => "Unable to locate AAR Group!");
        }
        $members = $groups[0]->getMembers(FALSE);
    }
    $members = array_unique($members);
    $res = array();
    foreach($members as $key => $member)
    {
        $user = $server->getUserByDN($member);
        if($user != FALSE)
        {
            array_push($res, array('legalName' => $user->givenName[0].' '.$user->sn[0],
                        'burnerName' => $user->displayName[0],
                        'title'=>title_to_string($user->title[0]),
                        'email'=>$user->mail[0],
                        'phone'=>$user->mobile[0],
                        'area'=>$user->ou[0]));
        }
    }
    return $res;
}

function array2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   fputcsv($df, array_keys(reset($array)));
   foreach ($array as $row) {
      fputcsv($df, $row);
   }
   fclose($df);
   return ob_get_clean();
}

echo array2csv(get_directory());

/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
