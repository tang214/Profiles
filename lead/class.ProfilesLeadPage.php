<?php
require_once('class.ProfilesPage.php');
require_once('class.FlipSession.php');
class ProfilesLeadPage extends FlipPage
{
    private $is_lead;

    function __construct($title)
    {
        parent::__construct($title);
        if($this->user == FALSE)
        {
            $this->is_lead = FALSE;
        }
        else
        {
            $this->is_lead = $this->user->isInGroupNamed('Leads');
            if(!$this->is_lead)
            {
                $this->is_lead = $this->user->isInGroupNamed('CC');
            }
        }
        $this->add_leads_css();
        $this->add_links();
        $this->add_js(JS_DATATABLE);
        $this->add_js(JQUERY_VALIDATE);
        $this->add_js(JS_METISMENU);
        $this->add_js_from_src('../_admin/js/admin.js');
        $this->add_js(JS_LOGIN);
    }

    function add_leads_css()
    {
        $this->add_css(CSS_DATATABLE);
        $this->add_css_from_src('../css/profiles.css');
        $this->add_css_from_src('css/lead.css');
    }

    function add_links()
    {
        if(!FlipSession::is_logged_in())
        {
            $this->add_link('Login', '../login.php');
        }
        else
        {
            $this->add_link('Logout', '../logout.php');
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
        $log = '';
        foreach($this->sites as $link => $site_name)
        {
            $sites .= '<li><a href="'.$site_name.'">'.$link.'</a></li>';
        }
        if(!FlipSession::is_logged_in())
        {
            $log = '<a href="../login.php?return='.$this->current_url().'"><span class="glyphicon glyphicon-log-in"></span></a>';
        }
        else
        {
            $log = '<a href="../logout.php"><span class="glyphicon glyphicon-log-out"></span></a>';
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
                          <a class="navbar-brand" href="index.php">Leads</a>
                      </div>
                      <ul class="nav navbar-top-links navbar-right links">
                           <a href="../">
                              <span class="glyphicon glyphicon-home"></span>
                           </a>
                           &nbsp;&nbsp;
                          '.$log.'
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
                                      <a href="#"><span class="glyphicon glyphicon-th-list"></span> Directory</a>
                                      <ul class="nav nav-second-level collapse">
                                          <li><a href="directory.php">All</a></li>
                                          <li><a href="directory.php?filter=aar">AAR</a></li>
                                          <li><a href="directory.php?filter=af">AFs</a></li>
                                          <li><a href="directory.php?filter=cc">CC</a></li>
                                          <li><a href="directory.php?filter=360">360/24/7 Department</a></li>
                                          <li><a href="directory.php?filter=Art">Art</a></li>
                                          <li><a href="directory.php?filter=CityPlanning">City Planning</a></li>
                                          <li><a href="directory.php?filter=Comm">Communications</a></li>
                                          <li><a href="directory.php?filter=Safety">Safety</a></li>
                                          <li><a href="directory.php?filter=site-ops">Site-Ops</a></li>
                                          <li><a href="directory.php?filter=siteprep">Site Prep</a></li>
                                          <li><a href="directory.php?filter=sign-off">Site Sign-Off</a></li>
                                          <li><a href="directory.php?filter=vc">Volunteer Coordinator</a></li>
                                      </ul>
                                  </li>
                              </ul>
                          </div>
                      </div>
                  </nav>
                  <div id="page-wrapper" style="min-height: 538px;">'.$this->body.'</div></div>';
        $this->add_login_form();
    }

    function current_url()
    {
        return 'http'.(isset($_SERVER['HTTPS'])?'s':'').'://'."{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}";
    }

    function print_page($header=true)
    {
        if($this->user == FALSE)
        {
            $this->body = '
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">You must <a href="/login.php?return='.$this->current_url().'">log in <span class="glyphicon glyphicon-log-in"></span></a> to access the Burning Flipside Profile Admin system!</h1>
            </div>
        </div>';
        }
        else if(!$this->is_lead)
        {
            $this->body = '
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">You must be a lead to access this page!</h1>
            </div>
        </div>';
        }
        parent::print_page(true);
    }

    function add_login_form()
    {
        $this->body .= '<div class="modal fade" role="dialog" id="login-dialog" title="Login" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">
                                            <span aria-hidden="true">&times;</span>
                                            <span class="sr-only">Close</span>
                                        </button>
                                        <h4 class="modal-title">Login</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form id="login_dialog_form" role="form">
                                            <input class="form-control" type="text" name="username" placeholder="Username or Email" required autofocus/>
                                            <input class="form-control" type="password" name="password" placeholder="Password" required/>
                                            <input type="hidden" name="return" value="'.$this->current_url().'"/>
                                            <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>';
    }
}
?>
