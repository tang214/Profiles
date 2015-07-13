<?php
require_once('class.FlipPage.php');
require_once('class.FlipSession.php');
class ProfilesPage extends FlipPage
{
    public $profiles_root;

    function __construct($title)
    {
        parent::__construct($title, true);
        $root = $_SERVER['DOCUMENT_ROOT'];
        $script_dir = dirname(__FILE__);
        $this->profiles_root = substr($script_dir, strlen($root));
        $this->add_profiles_css();
        $this->add_profiles_script();
        $this->add_links();
        $this->add_login_form();
        $this->body_tags='data-login-url="'.$this->profiles_root.'/api/v1/login"';
    }

    function add_profiles_css()
    {
        $this->add_css_from_src($this->profiles_root.'/css/profiles.css');
    }

    function add_profiles_script()
    {
        $this->add_js(JS_LOGIN);
    }

    function add_links()
    {
        if(!FlipSession::is_logged_in())
        {
            if(strstr($_SERVER['REQUEST_URI'], 'logout.php') === false)
            {
                $this->add_link('Login', $this->profiles_root.'/login.php');
            }
        }
        else
        {
            $this->user = FlipSession::get_user(TRUE);
            if($this->user !== false && $this->user->isInGroupNamed("LDAPAdmins"))
            {
                $this->add_link('Admin', $this->profiles_root.'/_admin/index.php');
            }
            if($this->user != FALSE && ($this->user->isInGroupNamed("Leads") || $this->user->isInGroupNamed("CC")))
            {
                $this->add_link('Leads', $this->profiles_root.'/lead/index.php');
            }
            $this->add_link('My Profile', $this->profiles_root.'/profile.php');
            $this->add_link('Logout', $this->profiles_root.'/logout.php');
        }
        $about_menu = array(
            'Burning Flipside'=>'http://www.burningflipside.com/about/event',
            'AAR, LLC'=>'http://www.burningflipside.com/LLC',
            'Privacy Policy'=>'http://www.burningflipside.com/about/privacy'
        );
        $this->add_link('About', 'http://www.burningflipside.com/about', $about_menu);
    }
}
?>
