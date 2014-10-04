<?php
require_once("class.FlipsideLDAPServer.php");
require_once("class.FlipJax.php");
require_once("_admin/class.FlipsideLead.php");
class DirectoryAjax extends FlipJaxSecure
{
    private $positions = null;

    function title_to_string($title)
    {
        if($this->positions == null)
        {
            $db = new FlipsideDB('registration');
            $this->positions = FlipsideLead::get_all_of_type($db);
            if($this->positions == FALSE)
            {
                return $title;
            }
            if(!is_array($this->positions))
            {
                $this->positions = array($this->positions);
            }
        }
        for($i = 0; $i < count($this->positions); $i++)
        {
            if($this->positions[$i]->short_name == $title)
            {
                return $this->positions[$i]->name;
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
                                     'title'=>$this->title_to_string($user->title[0]),
                                     'email'=>$user->mail[0],
                                     'phone'=>$user->mobile[0],
                                     'area'=>$user->ou[0]));
            }
        }
        return array('data'=>$res);
    }

    function get($params)
    {
        if(!$this->user_in_group('Leads'))
        {
            return array('err_code' => self::ACCESS_DENIED, 'reason' => "Must be a lead to access the directory!");
        }
        if(isset($params['filter']))
        {
            return $this->get_directory($params['filter']);
        }
        else
        {
            return $this->get_directory();
        }
    }
}

$ajax = new DirectoryAjax();
$ajax->run();
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>
