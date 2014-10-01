<?php
require_once('class.ProfilesPage.php');
require_once('class.FlipSession.php');
class ProfilesAdminPage extends FlipPage
{
    private $user;
    private $is_admin;

    function __construct($title)
    {
        $this->user = FlipSession::get_user(TRUE);
        if($this->user == FALSE)
        {
            $this->is_admin = FALSE;
        }
        else
        {
            $this->is_admin = $this->user->isInGroupNamed("LDAPAdmins");
        }
        parent::__construct($title);
        $this->add_css();
        $this->add_sites();
        $this->add_links();
        $this->add_js_from_src('/js/bootstrap-formhelpers.min.js');
        $this->add_js_from_src('js/jquery.dataTables.js');
        $this->add_js_from_src('js/metisMenu.min.js');
        $this->add_js_from_src('js/admin.js');
    }

    function add_css()
    {
        $css_tag = $this->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'/css/bootstrap-formhelpers.min.css', 'type'=>'text/css'), true);
        $this->add_head_tag($css_tag);

        $css_tag = $this->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'css/jquery.dataTables.css', 'type'=>'text/css'), true);
        $this->add_head_tag($css_tag);

        $css_tag = $this->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'/css/profiles.css', 'type'=>'text/css'), true);
        $this->add_head_tag($css_tag);

        $css_tag = $this->create_open_tag('link', array('rel'=>'stylesheet', 'href'=>'/_admin/css/admin.css', 'type'=>'text/css'), true);
        $this->add_head_tag($css_tag);
    }

    function add_sites()
    {
        $this->add_site('Profiles', 'http://profiles.burningflipside.com');
        $this->add_site('WWW', 'http://www.burningflipside.com');
        $this->add_site('Pyropedia', 'http://wiki.burningflipside.com');
        $this->add_site('Secure', 'https://secure.burningflipside.com');
    }

    function add_links()
    {
        if(!FlipSession::is_logged_in())
        {
            $this->add_link('Login', 'http://profiles.burningflipside.com/login.php');
        }
        else
        {
            if($this->is_admin == TRUE)
            {
                $this->add_link('Admin', 'https://profiles.burningflipside.com/_admin/');
            }
            $secure_menu = array(
                'Ticket Registration'=>'/tickets/index.php',
                'Ticket Transfer'=>'/tickets/transfer.php',
                'Theme Camp Registration'=>'/theme_camp/registration.php',
                'Art Project Registration'=>'/art/registration.php',
                'Event Registration'=>'/event/index.php'
            );
            $this->add_link('Secure', 'https://secure.burningflipside.com/', $secure_menu);
            $this->add_link('Logout', 'http://profiles.burningflipside.com/logout.php');
        }
        $about_menu = array(
            'Burning Flipside'=>'http://www.burningflipside.com/about/event',
            'AAR, LLC'=>'http://www.burningflipside.com/LLC',
            'Privacy Policy'=>'http://www.burningflipside.com/about/privacy'
        );
        $this->add_link('About', 'http://www.burningflipside.com/about', $about_menu);
    }

    function add_header()
    {
        $sites = '';
        foreach($this->sites as $link => $site_name)
        {
            $sites .= '<li><a href="'.$site_name.'">'.$link.'</a></li>';
        }
        $this->body = '<div id="wrapper">
                  <nav class="navbar navbar-default navbar-static-top" role=navigation" style="margin-bottom: 0">
                      <div class="navbar-header">
                          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                              <span class="sr-only">Toggle Navigation</span>
                              <span class="icon-bar"></span>
                              <span class="icon-bar"></span>
                              <span class="icon-bar"></span>
                          </button>
                          <a class="navbar-brand" href="index.php">Profiles</a>
                      </div>
                      <ul class="nav navbar-top-links navbar-right">
                           <a href="/">
                              <span class="glyphicon glyphicon-home"></span>
                           </a>
                          <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                  <span class="glyphicon glyphicon-link"></span>
                                  <b class="caret"></b>
                              </a>
                              <ul class="dropdown-menu dropdown-sites">
                                  '.$sites.'
                              </ul>
                          </li>
                      </ul>
                      <div class="navbar-default sidebar" role="navigation">
                          <div class="sidebar-nav navbar-collapse" style="height: 1px;">
                              <ul class="nav" id="side-menu">
                                  <li>
                                      <a href="index.php"><span class="glyphicon glyphicon-dashboard"></span> Dashboard</a>
                                  </li>
                                  <li>
                                      <a href="#"><span class="glyphicon glyphicon-user"></span> Users<span class="glyphicon arrow"></span></a>
                                      <ul class="nav nav-second-level collapse">
                                          <li><a href="users_current.php">Current</a></li>
                                          <li><a href="users_pending.php">Pending</a></li>
                                      </ul>
                                  </li>
                                  <li>
                                      <a href="groups.php"><span class="glyphicon glyphicon-tower"></span> Groups</span></a>
                                  </li>
                                  <li>
                                      <a href="sessions.php"><span class="glyphicon glyphicon-cloud"></span> Sessions</a>
                                  </li>
                              </ul>
                          </div>
                      </div>
                  </nav>
                  <div id="page-wrapper" style="min-height: 538px;">'.$this->body.'</div></div>';
    }

    function current_url()
    {
        return 'http'.(isset($_SERVER['HTTPS'])?'s':'').'://'."{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}";
    }

    function print_page()
    {
        if(!$this->is_admin)
        {
            $this->body = '
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">You must log in to access the Burning Flipside Profile Admin system!</h1>
            </div>
        </div>';
        }
        parent::print_page(true);
    }
}
?>
