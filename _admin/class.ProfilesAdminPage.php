<?php
require_once('class.FlipAdminPage.php');
require_once('class.FlipSession.php');

class ProfilesAdminPage extends FlipAdminPage
{
    function __construct($title)
    {
        parent::__construct($title, 'LDAPAdmins');
        $this->add_js_from_src('js/admin.js');
        $this->add_links();
    }

    function add_links()
    {
        if(!FlipSession::is_logged_in())
        {
            $this->add_link('Login', $this->login_url);
        }
        else
        {
            $users_menu = array(
                'Current' => 'users_current.php',
                'Pending' => 'users_pending.php'
            );
            $pos_menu = array(
                'Areas' => 'areas.php',
                'Leads' => 'leads.php'
            );
            $this->add_link('<span class="glyphicon glyphicon-dashboard"></span> Dashboard', 'index.php');
            $this->add_link('<span class="glyphicon glyphicon-user"></span> Users', '#', $users_menu);
            $this->add_link('<span class="glyphicon glyphicon-tower"></span> Groups', 'groups.php');
            $this->add_link('<span class="glyphicon glyphicon-briefcase"></span> Positions', '#', $pos_menu);
            $this->add_link('<span class="glyphicon glyphicon-cloud"></span> Sessions', 'sessions.php');
        }
    }

    function print_page($header = true)
    {
        if(!$this->is_admin)
        {
            $this->body = '
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">You must <a href="https://profiles.burningflipside.com/login.php?return='.$this->current_url().'">log in <span class="glyphicon glyphicon-log-in"></span></a> to access the Burning Flipside Profile Admin system!</h1>
            </div>
        </div>';
        }
        parent::print_page($header);
    }
}
?>
