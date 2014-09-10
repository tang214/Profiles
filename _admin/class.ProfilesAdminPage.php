<?php
require_once('class.FlipPage.php');
require_once('class.FlipSession.php');
class ProfilesAdminPage extends FlipPage
{
    public $user;

    function __construct($title)
    {
        parent::__construct($title, true);
        $this->add_css();
        $this->add_script();
        $this->add_sites();
        $this->add_links();
        $this->user = FlipSession::get_user(TRUE);
        if($this->user == FALSE || !$this->user->isInGroupNamed("LDAPAdmins"))
        {
            die('Not an administrator');
        }
    }

    function add_css()
    {
        $css_tag = $this->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'/css/profiles.css', 'type'=>'text/css'), true);
        $this->add_head_tag($css_tag);
        $css_tag = $this->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'/css/jquery-ui.css', 'type'=>'text/css'), true);
        $this->add_head_tag($css_tag);
        $css_tag = $this->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'css/jquery.dataTables.css', 'type'=>'text/css'), true);
        $this->add_head_tag($css_tag);
    }

    function add_sites()
    {
        $this->add_site('Profiles', 'https://profiles.burningflipside.com');
        $this->add_site('WWW', 'http://www.burningflipside.com');
        $this->add_site('Pyropedia', 'http://wiki.burningflipside.com');
        $this->add_site('Secure', 'https://secure.burningflipside.com');
    }

    function add_links()
    {
        $admin_menu = array(
            'Edit Groups'=>'group_edit.php',
            'Edit Users'=>'user_edit.php',
            'Edit CAPTCHAs'=>'capthca_edit.php'
        );
        $this->add_link('Admin', 'https://profiles.burningflipside.com/_admin/index.php', $admin_menu);
        $this->add_link('Logout', 'https://profiles.burningflipside.com/logout.php');
    }

    function add_script()
    {
        $script_start_tag = $this->create_open_tag('script', array('src'=>'js/jquery.dataTables.js'));
        $script_close_tag = $this->create_close_tag('script');
        $this->add_head_tag($script_start_tag.$script_close_tag);

        $script_start_tag = $this->create_open_tag('script', array('src'=>'js/users.js'));
        $this->add_head_tag($script_start_tag.$script_close_tag);

        $script_start_tag = $this->create_open_tag('script', array('src'=>'js/pending_users.js'));
        $this->add_head_tag($script_start_tag.$script_close_tag);

        $script_start_tag = $this->create_open_tag('script', array('src'=>'js/groups.js'));
        $this->add_head_tag($script_start_tag.$script_close_tag);

        $script_start_tag = $this->create_open_tag('script', array('src'=>'js/sessions.js'));
        $this->add_head_tag($script_start_tag.$script_close_tag);
    }
}
?>
