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
        $this->addLink('<i class="fa fa-tachometer"></i> Dashboard', 'index.php');
        $this->addLink('<i class="fa fa-user"></i> Users', '#', $users_menu);
        $this->addLink('<i class="fa fa-users"></i> Groups', 'groups.php');
        $this->addLink('<i class="fa fa-briefcase"></i> Positions', '#', $pos_menu);
        $this->addLink('<i class="fa fa-cloud"></i> Sessions', 'sessions.php');
    }
}
?>
