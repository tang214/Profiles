<?php
require_once('class.FlipAdminPage.php');
require_once('class.FlipSession.php');

class ProfilesAdminPage extends FlipAdminPage
{
    function __construct($title)
    {
        parent::__construct($title, 'LDAPAdmins');
        $this->addJSByURI('js/admin.js');
    }

    function add_links()
    {
        $users_menu = array(
            'Current' => 'users_current.php',
            'Pending' => 'users_pending.php'
        );
        $pos_menu = array(
            'Areas' => 'areas.php',
            'Leads' => 'leads.php'
        );
        $this->addLink('<span class="glyphicon glyphicon-dashboard"></span> Dashboard', 'index.php');
        $this->addLink('<span class="glyphicon glyphicon-user"></span> Users', '#', $users_menu);
        $this->addLink('<span class="glyphicon glyphicon-tower"></span> Groups', 'groups.php');
        $this->addLink('<span class="glyphicon glyphicon-briefcase"></span> Positions', '#', $pos_menu);
        $this->addLink('<span class="glyphicon glyphicon-cloud"></span> Sessions', 'sessions.php');
    }
}
?>
